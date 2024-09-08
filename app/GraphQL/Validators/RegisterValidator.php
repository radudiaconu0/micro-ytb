<?php

namespace App\GraphQL\Validators;

use Nuwave\Lighthouse\Schema\Validator;

class RegisterValidator extends Validator
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
