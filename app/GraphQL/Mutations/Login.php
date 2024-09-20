<?php

namespace App\GraphQL\Mutations;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use GraphQL\Error\Error;
use Nette\Schema\ValidationException;
use function Pest\Laravel\withCookie;

class Login
{
    /**
     * @throws Error
     */
    public function __invoke($_, array $args)
    {
        try {
            $credentials = [
                'email' => $args['email'],
                'password' => $args['password'],
            ];

            if (!$token = auth()->attempt($credentials)) {
                throw new Error('Invalid email or password.');
            }

            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            throw new Error('An error occurred during login. Please try again.');
        }
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
