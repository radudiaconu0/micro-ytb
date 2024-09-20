<?php

namespace App\Models;

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
}
