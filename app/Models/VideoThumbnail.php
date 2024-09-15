<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class VideoThumbnail extends Model
{
    protected $fillable = [
        'video_id',
        's3_key',
        'size',
        'width',
        'height',
    ];

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function apiObject(): array
    {
        return [
            'thumbnail_url' => Storage::disk('s3')->temporaryUrl('thumbnails/' . $this->s3_key, now()->addMinutes(5)),
            'width' => $this->width,
            'height' => $this->height,
            'size' => $this->size,
        ];
    }
}
