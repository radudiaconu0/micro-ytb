<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
