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
        $comment->comment_id = null;
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
        $comment->comment_id = $args['comment_id'] ?? null;
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

    public function createReply($_, array $args)
    {
        $commentToReply = \App\Models\Comment::find($args['comment_id']);
        $comment = new \App\Models\Comment();
        $comment->user_id = auth()->id();
        $comment->comment_id = $args['comment_id'];
        $comment->text = $args['body'];
        $comment->video_id = $commentToReply->video_id;
        $comment->save();
        return $comment;
    }
}
