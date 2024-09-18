<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoRequest;
use App\Jobs\ProcessVideoJob;
use App\Models\Video;
use App\Models\VideoThumbnail;
use FFMpeg\FFProbe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Laravel\Facades\Image;
use ProtoneMedia\LaravelFFMpeg\Filters\WatermarkFactory;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Str;

class VideoController extends Controller
{
    public function index()
    {
        return Video::all();
    }

    public function store(VideoRequest $request)
    {

        $video = new Video();


        $video->user_id = auth()->id();

        $video->title = $request->title;
        $video->description = $request->description;

        $watermark = $request->file('watermark_image');
        $watermarkType = $request->watermark_type;
        $watermarkPosition = $request->watermark_position;
        $watermarkText = $request->watermark_text;

        $videoCode = Str::random(11);
        $video->video_code = $videoCode;
        $fileName = Str::uuid()->toString() . '_' . time() . '.' . $request->file('video_file')->getClientOriginalExtension();
        $s3Key = $fileName;

        if ($watermarkType === 'image') {
            $watermarkFileName = Str::uuid()->toString() . '_' . time() . '.' . $watermark->getClientOriginalExtension();
            $watermarkS3Key = $watermarkFileName;
            Storage::disk('s3')->putFileAs(
                'watermarks',
                $watermark,
                $watermarkFileName
            );
            $video->watermark_type = $watermarkType;
            $video->watermark_content = $watermarkS3Key;
        }

        if ($watermarkType === 'text') {
            $video->watermark_type = $watermarkType;
            $video->watermark_content = $watermarkText;
        }

        $video->watermark_position = $watermarkPosition;


        Storage::disk('s3')->putFileAs(
            'videos/original',
            $request->file('video_file'),
            $fileName
        );

        $video->original_s3_key = $s3Key;
        $video->status = 'processing';
        $video->save();
        $this->generateThumbnails($video, $request->file('thumbnail_image'));

        ProcessVideoJob::dispatch($video);
        return response()->json([
            'success' => true,
        ]);
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
        \Validator::make(
            $request->all(),
            [
                'video_code' => 'required|exists:videos,video_code',
                'title' => 'required',
                'description' => 'required',
                'thumbnail_image' => 'image',
            ]
        )->validate();
        $video = Video::whereVideoCode($request->video_code)->firstOrFail();
        $video->description = $request->description;
        $video->title = $request->title;
        if ($request->has('thumbnail_image')) {
            $thumbnails = $video->thumbnails;
            foreach ($thumbnails as $thumbnail) {
                Storage::disk('s3')->delete('thumbnails/' . $thumbnail->s3_key);
                $thumbnail->delete();
            }
            $this->generateThumbnails($video, $request->file('thumbnail_image'));
        }
        $video->save();
        return response()->json([
            'success' => true,
        ]);
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
