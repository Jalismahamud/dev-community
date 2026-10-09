<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->route('post')?->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'body_markdown' => ['required', 'string', 'max:100000'],
            'tag_ids' => ['array'],
            'tag_ids.*' => ['integer', 'exists:tech_tags,id'],
        ];
    }
}
