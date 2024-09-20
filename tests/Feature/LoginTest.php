<?php

namespace Tests\Feature\GraphQL;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;

    private string $loginMutation = '
        mutation Login($email: String!, $password: String!) {
            login(email: $email, password: $password) {
                access_token
                token_type
                expires_in
            }
        }
    ';

    public function testSuccessfulLogin(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->graphQL($this->loginMutation, [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertJsonStructure([
            'data' => [
                'login' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                ],
            ],
        ]);

        $responseData = $response->json('data.login');
        $this->assertIsString($responseData['access_token']);
        $this->assertEquals('bearer', $responseData['token_type']);
        $this->assertIsInt($responseData['expires_in']);
    }

    public function testInvalidCredentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->graphQL($this->loginMutation, [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertJsonStructure([
            'errors' => [
                [
                    'message',
                ],
            ],
        ]);

        $this->assertStringContainsString('Invalid email or password.', $response->json('errors.0.message'));
    }

    public function testLoginError(): void
    {

        $response = $this->graphQL($this->loginMutation, [
            'email' => 'nonexistent@example.com',
            'password' => 'somepassword',
        ]);

        $response->assertJsonStructure([
            'errors' => [
                [
                    'message',
                ],
            ],
        ]);
        $this->assertStringContainsString('An error occurred during login:', $response->json('errors.0.message'));
    }
}
