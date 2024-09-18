<?php

namespace App\GraphQL\Mutations;
class Comment
{
    public function createComment($_, array $args)
    {
        $comment = new \App\Models\Comment();
        $comment->user_id = auth()->id();
        $comment->comment_id = $args['comment_id'];
        $comment->text = $args['text'];
        $comment->video_id = $args['video_id'];
        $comment->save();
        return $comment;
    }

    public function updateComment($_, array $args)
    {
        $comment = \App\Models\Comment::find($args['id']);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        $comment->user_id = auth()->id();
        $comment->comment_id = $args['comment_id'];
        $comment->text = $args['text'];
        $comment->video_id = $args['video_id'];
        $comment->save();
        return $comment;
    }

    // deleteComment mutation

    public function deleteComment($_, array $args)
    {
        $comment = \App\Models\Comment::find($args['id']);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        $comment->delete();
        return $comment;
    }

    public function createReply($_, array $args)
    {
        $comment = new \App\Models\Comment();
        $comment->user_id = auth()->id();
        $comment->comment_id = $args['comment_id'];
        $comment->text = $args['text'];
        $comment->video_id = $args['video_id'];
        $comment->save();
        return $comment;
    }
}
