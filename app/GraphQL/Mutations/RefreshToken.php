<?php

namespace App\GraphQL\Mutations;

class RefreshToken
{
    public function __invoke($rootValue, array $args)
    {
        return response()->json([
            'access_token' => auth()->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
