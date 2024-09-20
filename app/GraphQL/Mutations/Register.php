<?php

namespace App\GraphQL\Mutations;

use App\Models\User;

class Register
{
    public function __invoke($_, array $args)
    {
        $user = new User();
        $user->name = $args['name'];
        $user->email = $args['email'];
        $user->password = bcrypt($args['password']);
        $user->save();
        $creds = [
            'email' => $args['email'],
            'password' => $args['password']
        ];
        $token = auth()->attempt($creds);


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
