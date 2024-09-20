<?php

namespace App\GraphQL\Mutations;

use App\Models\Video;

class Comment
{
    public function createComment($_, array $args)
    {
        $video = Video::whereVideoCode($args['video_code'])->first();
        $comment = new \App\Models\Comment();
        $comment->user_id = auth()->id();
        $comment->text = $args['body'];
        $comment->video_id = $video->id;
        $comment->save();
        return $comment;
    }

    public function updateComment($_, array $args)
    {
        $comment = \App\Models\Comment::find($args['comment_id']);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        $comment->user_id = auth()->id();
        $comment->text = $args['body'];
        $comment->save();
        return $comment;
    }

    public function deleteComment($_, array $args)
    {
        $comment = \App\Models\Comment::find($args['comment_id']);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        $comment->delete();
        return $comment;
    }
}
