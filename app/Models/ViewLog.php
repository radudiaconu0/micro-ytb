<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ViewLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'video_id',
        'ip_address',
        'user_agent',
    ];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(config('app.display_timezone'))->format('Y-m-d H:i:s');
    }
}
