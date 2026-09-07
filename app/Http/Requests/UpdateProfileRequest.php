<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array { return ['name' => ['required', 'string', 'max:100'], 'bio' => ['nullable', 'string', 'max:2000'], 'github_url' => ['nullable', 'url', 'max:255'], 'linkedin_url' => ['nullable', 'url', 'max:255'], 'current_status' => ['nullable', 'string', 'max:255'], 'tag_ids' => ['array'], 'tag_ids.*' => ['integer', 'exists:tech_tags,id'], 'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240']]; }
}