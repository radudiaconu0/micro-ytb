<?php

namespace App\GraphQL\Queries;

class Me
{
    public function __invoke($_, array $args)
    {
        return auth()->user();
    }
}
