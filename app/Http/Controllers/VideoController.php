<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoRequest;
use App\Jobs\ProcessVideoJob;
use App\Models\Video;
use App\Models\VideoThumbnail;
use FFMpeg\FFProbe;
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

        ProcessVideoJob::dispatch($video);
        return response()->json([
            'success' => true,
        ]);
    }
}
