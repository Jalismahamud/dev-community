<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array { return ['title' => ['nullable', 'string', 'max:255'], 'body_markdown' => ['required', 'string', 'max:100000'], 'tag_ids' => ['array'], 'tag_ids.*' => ['integer', 'exists:tech_tags,id'], 'images' => ['array', 'max:10'], 'images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240']]; }
}