<?php

namespace App\GraphQL\Mutations;

use App\Models\Video;
use Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Nette\Schema\ValidationException;
use Validator;

class Comment
{
    /**
     * @throws AuthenticationException
     */
    public function createComment($_, array $args)
    {
        $this->validateUser();

        $video = Video::whereVideoCode($args['video_code'])->firstOrFail();

        $validator = Validator::make($args, [
            'body' => ['required', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $comment = new \App\Models\Comment();
        $comment->user_id = Auth::id();
        $comment->text = $args['body'];
        $comment->video_id = $video->id;
        $comment->save();

        return $comment;
    }

    public function updateComment($_, array $args)
    {
        $this->validateUser();

        $comment = \App\Models\Comment::findOrFail($args['comment_id']);

        if ($comment->user_id !== Auth::id()) {
            throw new AuthorizationException('You are not authorized to update this comment.');
        }

        $validator = Validator::make($args, [
            'body' => ['required', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $comment->text = $args['body'];
        $comment->save();

        return $comment;
    }

    public function deleteComment($_, array $args)
    {
        $this->validateUser();

        $comment = \App\Models\Comment::findOrFail($args['comment_id']);

        if ($comment->user_id !== Auth::id()) {
            throw new AuthorizationException('You are not authorized to delete this comment.');
        }

        $comment->delete();

        return $comment;
    }

    /**
     * @throws AuthenticationException
     */
    private function validateUser(): void
    {
        if (!Auth::check()) {
            throw new \Illuminate\Auth\AuthenticationException('User must be logged in to perform this action.');
        }
    }
}
