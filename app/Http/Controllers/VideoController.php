<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoRequest;
use App\Jobs\ProcessVideoJob;
use App\Models\Video;
use App\Models\VideoThumbnail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Laravel\Facades\Image;
use Log;
use Str;

class VideoController extends Controller
{
    public function index()
    {
        return Video::all();
    }

    public function store(VideoRequest $request)
    {
        try {
            $video = new Video();
            $video->user_id = auth()->id();
            $video->title = $request->title;
            $video->description = $request->description;

            $watermarkType = $request->watermark_type;
            $watermarkPosition = $request->watermark_position;
            $watermarkText = $request->watermark_text;

            $videoCode = Str::random(11);
            $video->video_code = $videoCode;

            if (!$request->hasFile('video_file') || !$request->file('video_file')->isValid()) {
                return response()->json(['errors' => ['video_file' => ['The video file is invalid or missing.']]], 422);
            }

            $fileName = Str::uuid()->toString() . '_' . time() . '.' . $request->file('video_file')->getClientOriginalExtension();
            $s3Key = $fileName;

            if ($watermarkType === 'image') {
                if (!$request->hasFile('watermark_image') || !$request->file('watermark_image')->isValid()) {
                    return response()->json(['errors' => ['watermark_image' => ['The watermark image is invalid or missing.']]], 422);
                }

                $watermark = $request->file('watermark_image');
                $watermarkFileName = Str::uuid()->toString() . '_' . time() . '.' . $watermark->getClientOriginalExtension();
                $watermarkS3Key = $watermarkFileName;

                try {
                    Storage::disk('s3')->putFileAs('watermarks', $watermark, $watermarkFileName);
                } catch (\Exception $e) {
                    Log::error('Failed to upload watermark image: ' . $e->getMessage());
                    return response()->json(['errors' => ['watermark_image' => ['Failed to upload watermark image.']]], 500);
                }

                $video->watermark_type = $watermarkType;
                $video->watermark_content = $watermarkS3Key;
            } elseif ($watermarkType === 'text') {
                if (empty($watermarkText)) {
                    return response()->json(['errors' => ['watermark_text' => ['Watermark text is required when using text watermark.']]], 422);
                }
                $video->watermark_type = $watermarkType;
                $video->watermark_content = $watermarkText;
            }

            $video->watermark_position = $watermarkPosition;

            try {
                Storage::disk('s3')->putFileAs('videos/original', $request->file('video_file'), $fileName);
            } catch (\Exception $e) {
                Log::error('Failed to upload video file: ' . $e->getMessage());
                return response()->json(['errors' => ['video_file' => ['Failed to upload video file.']]], 500);
            }

            $video->original_s3_key = $s3Key;
            $video->status = 'processing';
            $video->save();

            try {
                $this->generateThumbnails($video, $request->file('thumbnail_image'));
            } catch (\Exception $e) {
                Log::error('Failed to generate thumbnails: ' . $e->getMessage());
            }

            ProcessVideoJob::dispatch($video)->onQueue('video-processing');

            return response()->json([
                'success' => true,
                'message' => 'Video uploaded successfully and is now being processed.',
                'video_code' => $video->video_code
            ]);
        } catch (\Exception $e) {
            Log::error('Video upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while processing your video.',
            ], 500);
        }
    }

    public function generateThumbnails($video, $thumbnailFile)
    {
        $fileName = Str::uuid()->toString() . '_' . time();
        $image = Image::read($thumbnailFile);

        $qualities = [
            'small' => ['width' => 1280, 'quality' => 80],
            'medium' => ['width' => 640, 'quality' => 60],
            'large' => ['width' => 320, 'quality' => 40],
        ];


        foreach ($qualities as $quality => $specs) {
            $height = round(($specs['width'] / $image->width()) * $image->height());

            $resized = $image->scale($specs['width'], $height);

            $thumbnailFilename = "{$fileName}_{$quality}.jpg";


            Storage::disk('s3')->put(
                "thumbnails/{$thumbnailFilename}",
                $resized->toJpeg($specs['quality'])->toFilePointer()
            );

            VideoThumbnail::create([
                'video_id' => $video->id,
                's3_key' => $thumbnailFilename,
                'size' => $quality,
                'height' => $height,
                'width' => $specs['width'],
            ]);
        }
    }

    public function updateVideo(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'video_code' => 'required|exists:videos,video_code',
                'title' => 'required|max:255',
                'description' => 'required|max:1000',
                'thumbnail_image' => 'nullable|image|max:2048', // 2MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $video = Video::whereVideoCode($request->video_code)->firstOrFail();

            if ($video->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this video'
                ], 403);
            }

            $video->description = $request->description;
            $video->title = $request->title;

            if ($request->hasFile('thumbnail_image')) {
                $thumbnails = $video->thumbnails;
                foreach ($thumbnails as $thumbnail) {
                    // Check if file exists before attempting to delete
                    if (Storage::disk('s3')->exists('thumbnails/' . $thumbnail->s3_key)) {
                        Storage::disk('s3')->delete('thumbnails/' . $thumbnail->s3_key);
                    }
                    $thumbnail->delete();
                }
                $this->generateThumbnails($video, $request->file('thumbnail_image'));
            }

            $video->save();

            return response()->json([
                'success' => true,
                'message' => 'Video updated successfully',
                'data' => $video
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Video not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error updating video: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the video'
            ], 500);
        }
    }

    public function fetchVideo($videoCode)
    {
        $video = Video::whereVideoCode($videoCode)->first();
        if (!$video) {
            return response()->json([
                'success' => false
            ], 404);
        }

        return response()->json([
            'data' => $video->apiObject(),
            'success' => false
        ]);
    }
}
