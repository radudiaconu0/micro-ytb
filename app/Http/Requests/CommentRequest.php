<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
            'comment_id' => ['required', 'integer'],
            'text' => ['required'],
            'video_id' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
