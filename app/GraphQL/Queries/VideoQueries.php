<?php

namespace App\GraphQL\Queries;

use App\Models\Video;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelIdea\Helper\App\Models\_IH_Video_C;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class VideoQueries
{
    public function feedVideos($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array|LengthAwarePaginator|_IH_Video_C
    {
        return Video::latest()
            ->paginate($args['first'], ['*'], 'page', $args['page'] ?? 1);
    }

    public function myVideos($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = $context->user();

        return $user->videos
            ->latest()
            ->paginate($args['first'], ['*'], 'page', $args['page'] ?? 1);
    }

}
