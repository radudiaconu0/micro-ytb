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
            \Log::info('Login attempt for email: ' . $args['email']);
            $credentials = [
                'email' => $args['email'],
                'password' => $args['password'],
            ];

            if (!$token = auth()->attempt($credentials)) {
                \Log::warning('Failed login attempt for email: ' . $args['email']);
                throw new Error('Invalid email or password.');
            }

            \Log::info('Successful login for email: ' . $args['email']);
            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw new Error('An error occurred during login: ' . $e->getMessage());
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
