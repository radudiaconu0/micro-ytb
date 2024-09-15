<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'video_file' => 'required|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime',
            'title' => 'required|string',
            'description' => 'required|string',
            'thumbnail_image' => 'nullable',
            'watermark_type' => 'nullable|in:text,image',
            'watermark_text' => 'nullable|string|required_if:watermark_type,text',
            'watermark_image' => 'nullable|required_if:watermark_type,image',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
