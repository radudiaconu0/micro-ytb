<?php

namespace App\GraphQL\Mutations;

use App\Models\ViewLog;

class Video
{
    public function incrementViews($rootValue, array $args, $context)
    {
        $video = \App\Models\Video::where('video_code', $args['video_code'])->firstOrFail();
        $recentView = ViewLog::where('video_id', $video->id)
            ->where('ip_address', $context->request()->ip())
            ->where('created_at', '>', now()->subMinutes(10))
            ->first();

        if (!$recentView) {
            $video->increment('views');

            ViewLog::create([
                'video_id' => $video->id,
                'ip_address' => $context->request()->ip(),
                'user_agent' => $context->request()->userAgent()
            ]);

            return [
                'success' => true,
                'message' => 'View count incremented successfully.',
                'views_count' => $video->views
            ];
        }

        return [
            'success' => false,
            'message' => 'View already counted within the last 24 hours.',
            'views_count' => $video->views
        ];
    }
}
