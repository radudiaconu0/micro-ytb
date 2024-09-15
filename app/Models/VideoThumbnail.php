<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class VideoThumbnail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'video_id',
        's3_key',
        'width',
        'height',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function apiObject(): array
    {
        return [
            'thumbnail_url' => Storage::disk('s3')->url($this->s3_key),
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
