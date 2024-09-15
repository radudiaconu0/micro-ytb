<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoThumbnailRequest;
use App\Models\VideoThumbnail;

class VideoThumbnailController extends Controller
{
    public function index()
    {
        return VideoThumbnail::all();
    }

    public function store(VideoThumbnailRequest $request)
    {
        return VideoThumbnail::create($request->validated());
    }

    public function show(VideoThumbnail $videoThumbnail)
    {
        return $videoThumbnail;
    }

    public function update(VideoThumbnailRequest $request, VideoThumbnail $videoThumbnail)
    {
        $videoThumbnail->update($request->validated());

        return $videoThumbnail;
    }

    public function destroy(VideoThumbnail $videoThumbnail)
    {
        $videoThumbnail->delete();

        return response()->json();
    }
}
