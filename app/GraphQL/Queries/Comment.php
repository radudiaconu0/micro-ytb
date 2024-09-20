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
        $comments =  $video->comments()->with('user')->paginate(10);
        return [
            'data' => $comments->items(),  // Items (video data) from pagination
            'paginatorInfo' => [
                'total' => $comments->total(),
                'count' => $comments->count(),
                'perPage' => $comments->perPage(),
                'currentPage' => $comments->currentPage(),
                'lastPage' => $comments->lastPage(),
            ],
        ];
    }
}
