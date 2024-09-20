<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;

class Video extends Model
{
    use SoftDeletes, Searchable, HasFactory;

    protected $fillable = [
        'video_code',
        'user_id',
        'title',
        'description',
        'original_s3_key',
        'processed_s3_key',
        'watermark_type',
        'watermark_content',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function thumbnails(): HasMany
    {
        return $this->hasMany(VideoThumbnail::class);
    }

    public function apiObject(): array
    {
        return [
            'video_code' => $this->video_code,
            'url' => Storage::disk('s3')->temporaryUrl('videos/processed/' . $this->processed_s3_key, now()->addMinutes(5)),
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'views' => $this->views,
            'user' => $this->user->apiObject(),
            'thumbnails' => $this->thumbnails->map(fn(VideoThumbnail $thumbnail) => $thumbnail->apiObject()),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->getKey(),
            'title' => $this->title,
            'description' => $this->description,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === 'processed';
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'video_id');
    }
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(config('app.display_timezone'))->format('Y-m-d H:i:s');
    }
}
