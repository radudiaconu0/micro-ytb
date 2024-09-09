<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoRequest;
use App\Models\Video;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class VideoController extends Controller
{
    public function index()
    {
        return Video::all();
    }

    public function store(VideoRequest $request)
    {
        // store the video in the bucket
        $video = $request->file('file');
        $videoName = $video->getClientOriginalName();
        // get full metadata
        $metadata = FFMpeg::fromDisk('local')
            ->open($videoName)
            ->getFFProbe()
            ->all();
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
