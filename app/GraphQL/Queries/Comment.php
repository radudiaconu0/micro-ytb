<?php

namespace App\GraphQL\Queries;

class Comment
{
    public function comments($_, array $args)
    {
        $video = \App\Models\Video::where('video_code', $args['videoCode'])->first();
        if (!$video) {
            return response()->json(['message' => 'Video not found'], 404);
        }
        return $video->comments()->paginate(10);
    }

    public function replies($_, array $args)
    {
        $comment = \App\Models\Comment::find($args['commentId']);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        return $comment->replies()->paginate(10);
    }

}
