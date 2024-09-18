<?php

use App\Http\Controllers\VideoController;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/store-video', [VideoController::class, 'store'])->middleware('auth:api');

Route::get('/videos', [VideoController::class, 'getVideos']);

Route::get('/search-videos', function (Request $request) {
    $videos = Video::search($request->query('query'))->paginate(10);
    return response()->json($videos);
});

Route::post('/videos/update', [VideoController::class, 'updateVideo'])->middleware('auth:api');
Route::get('/videos/{videoCode}', [VideoController::class, 'fetchVideo'])->middleware('auth:api');
