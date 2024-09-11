<?php

namespace App\GraphQL\Mutations;

class Logout
{
    public function __invoke($_, array $args)
    {
        auth()->logout();

        return [
            'message' => 'Successfully logged out'
        ];
    }
}
