<?php

namespace App\GraphQL\Mutations;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use GraphQL\Error\Error;
use Nette\Schema\ValidationException;

class Login
{
    /**
     * @throws Error
     */
    public function __invoke($_, array $args)
    {
        $credentials = [
            'email' => $args['email'],
            'password' => $args['password'],
        ];

        if (!$token = auth()->attempt($credentials)) {
            throw new Error('Credentials are invalid.');
        }

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token): array
    {
        \Log::info($token);
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ];
    }
}
