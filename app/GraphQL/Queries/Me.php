<?php

namespace App\GraphQL\Queries;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class Me
{
    public function __invoke($_, array $args): User|Authenticatable|null
    {
        \Log::info(auth('api')->user());
        return auth('api')->user();
    }
}
