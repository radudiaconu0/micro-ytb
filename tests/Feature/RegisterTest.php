<?php

namespace Tests\Feature\GraphQL;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;

    private string $registerMutation = '
        mutation Register($name: String!, $email: String!, $password: String!, $password_confirmation: String!) {
        register(name: $name, email: $email, password: $password, password_confirmation: $password_confirmation) {
            access_token
            expires_in
            token_type
        }
    }
    ';

    public function testSuccessfulRegistration(): void
    {
        $response = $this->graphQL($this->registerMutation, [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        if (isset($response['errors'])) {
            $this->fail('GraphQL errors: ' . json_encode($response['errors']));
        }

        $response->assertJsonStructure([
            'data' => [
                'register' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                ],
            ],
        ]);

        $responseData = $response->json('data.register');
        $this->assertIsString($responseData['access_token']);
        $this->assertEquals('bearer', $responseData['token_type']);
        $this->assertIsInt($responseData['expires_in']);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
        ]);
    }

    public function testRegistrationWithExistingEmail(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->graphQL($this->registerMutation, [
            'name' => 'Jane Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertJsonStructure([
            'errors' => [
                [
                    'message',
                ],
            ],
        ]);

        $this->assertStringContainsString('The email has already been taken.', $response->json('errors.0.extensions.validation.email.0'));
    }

    public function testRegistrationWithInvalidEmail(): void
    {
        $response = $this->graphQL($this->registerMutation, [
            'name' => 'Invalid User',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertJsonStructure([
            'errors' => [
                [
                    'message',
                ],
            ],
        ]);

        $this->assertStringContainsString('The email field must be a valid email address.', $response->json('errors.0.extensions.validation.email.0'));
    }
}
