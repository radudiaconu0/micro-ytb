<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoRequest;
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

        dd($request->all());

        $video = new Video();
        $thumbnail = $request->file('thumbnail_image');
        $watermark = $request->file('watermark_image');
        $video_code = \Str::random(11);
        $video_file_name = 'VID_' . $video_code . '.' . 'mp4';

        $video_file = $request->file('video_file');

        $video_file->storeAs('videos', $video_file_name);

        if ($thumbnail) {
            $thumbnail = Image::read($thumbnail);
            $thumbnail_name = 'thumbnails/THUMBNAIL_' . $video_code . '.' . '.png';
            $thumbnail->save($thumbnail_name);
        }

        if ($watermark) {
            $watermark = Image::read($watermark);
            $watermark_name = 'watermarks/WATERMARK_' . $video_code . '.' . '.png';
            $watermark->save($watermark_name);
            $video->watermark_image_path = $watermark_name;
        }

        $video->description = $request->description;
        $video->title = $request->title;
        $video->thumbnail_type = $request->thumbnail_type;
        $video->video_file_path = $video_file_name;

        $video->save();


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
