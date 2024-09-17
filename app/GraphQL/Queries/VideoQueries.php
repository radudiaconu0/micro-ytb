<?php

namespace App\GraphQL\Queries;

use App\Models\Video;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelIdea\Helper\App\Models\_IH_Video_C;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class VideoQueries
{
    public function feedVideos($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        $videos = Video::with('user', 'thumbnails')
            ->latest()
            ->paginate($args['first'], ['*'], 'page', $args['page'] ?? 1);
        $videos->getCollection()->transform(function (Video $video) {
            return $video->apiObject();
        });

        return [
            'data' => $videos->items(),  // Items (video data) from pagination
            'paginatorInfo' => [
                'total' => $videos->total(),
                'count' => $videos->count(),
                'perPage' => $videos->perPage(),
                'currentPage' => $videos->currentPage(),
                'lastPage' => $videos->lastPage(),
            ],
        ];
    }

    public function myVideos($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = $context->user();

        return $user->videos
            ->latest()
            ->paginate($args['first'], ['*'], 'page', $args['page'] ?? 1);
    }

    public function searchVideos($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $videos = Video::search($args['query'])
            ->paginate(10);

        $videos->getCollection()->transform(function (Video $video) {
            return $video->apiObject();
        });

        return [
            'data' => $videos->items(),
            'paginatorInfo' => [
                'total' => $videos->total(),
                'count' => $videos->count(),
                'perPage' => $videos->perPage(),
                'currentPage' => $videos->currentPage(),
                'lastPage' => $videos->lastPage(),
            ],
        ];
    }

    public function fetchVideo($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return Video::whereVideoCode($args['video_code'])->firstOrFail()->apiObject();
    }
}
