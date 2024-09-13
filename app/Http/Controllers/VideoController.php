<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoRequest;
use App\Jobs\ProcessVideoJob;
use App\Models\Video;
use Intervention\Image\ImageManager;
use Intervention\Image\Laravel\Facades\Image;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class VideoController extends Controller
{
    public function index()
    {
        return Video::all();
    }

    public function store(VideoRequest $request)
    {

        $video = new Video();
        $thumbnail = $request->file('thumbnail_image');
        $watermark = $request->file('watermark_image');
        $video_code = \Str::random(11);
        $video_file_name = 'VID_' . $video_code . '.' . 'mp4';

        $video_file = $request->file('video_file');

        // Save video file in s3
        $video_file->storeAs('videos', $video_file_name, 's3');

        if ($thumbnail) {
            $thumbnail = Image::read($request->file('thumbnail_image'));
            $thumbnail_name = 'thumbnails/THUMBNAIL_' . $video_code . '.' . '.png';
            \Storage::disk('s3')->put($thumbnail_name, $thumbnail->encode());
            $video->thumbnail_image_path = $thumbnail_name;
        }

        if ($watermark) {
            $watermark = Image::read($request->file('watermark_image'));
            $watermark_name = 'watermarks/WATERMARK_' . $video_code . '.' . '.png';
            \Storage::disk('s3')->put($watermark_name, $watermark->encode());
            $video->watermark_image_path = $watermark_name;
        }

        $video->description = $request->description;
        $video->title = $request->title;
        $video->video_file_path = $video_file_name;
        $video->video_code = $video_code;
        $video->watermark_position = $request->watermark_position;
        $video->watermark_type = $request->watermark_type;
        $video->watermark_text = $request->watermark_text;
        $video->user_id = auth()->id();

//        $metadata = FFMpeg::fromDisk('s3')
//            ->open($video_file_name)->getStreams()->first();

        $info = [
            'duration' => 100
        ];

        $video->metadata = json_encode($info);

        $video->save();

        ProcessVideoJob::dispatch($video);


        return response()->json([
            'success' => true,
        ]);

    }

    public function show(Video $video)
    {
        return $video;
    }

    public function update(VideoRequest $request, Video $video)
    {
        $video->update($request->validated());

        return $video;
    }

    public function destroy(Video $video)
    {
        $video->delete();

        return response()->json();
    }
}
