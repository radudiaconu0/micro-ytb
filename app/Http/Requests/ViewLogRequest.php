<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ViewLogRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'video_id' => ['required', 'integer'],
            'ip_address' => ['required'],
            'user_agent' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
