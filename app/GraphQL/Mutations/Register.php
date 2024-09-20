<?php

namespace App\GraphQL\Mutations;

use App\Models\User;
use GraphQL\Error\Error;

class Register
{
    /**
     * @throws \Exception
     */
    public function __invoke($_, array $args)
    {
        try {
            $user = User::create([
                'name' => $args['name'],
                'email' => $args['email'],
                'password' => bcrypt($args['password']),
            ]);

            $credentials = [
                'email' => $args['email'],
                'password' => $args['password'],
            ];

            if (!$token = auth()->attempt($credentials)) {
                \Log::warning('Failed register attempt for email: ' . $args['email']);
                throw new Error('Error creating user.');
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
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ];
    }
}
