<?php

namespace App\GraphQL\Queries;

use App\Models\Video;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Comment
{
    public function comments($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {

        $video = Video::where('video_code', $args['video_code'])->first();
        if (!$video) {
            return response()->json(['message' => 'Video not found'], 404);
        }
        return $video->comments()->paginate(10);
    }

    public function replies($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $comment = \App\Models\Comment::find($args['comment_id']);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        return $comment->replies()->paginate(10);
    }

}
