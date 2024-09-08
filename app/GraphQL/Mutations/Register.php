<?php

namespace App\GraphQL\Mutations;

class Register
{
    public function __invoke($_, array $args)
    {
        $user = new \App\Models\User();
        $user->name = $args['name'];
        $user->email = $args['email'];
        $user->password = bcrypt($args['password']);
        $user->save();

        if (!$token = auth()->attempt($args)) {
            throw new \GraphQL\Error\Error('Credentials are invalid.');
        }

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
