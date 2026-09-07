<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array { return ['post_id' => ['nullable', 'integer', 'exists:posts,id', 'required_without:comment_id'], 'comment_id' => ['nullable', 'integer', 'exists:comments,id', 'required_without:post_id'], 'reason' => ['required', 'string', 'max:2000']]; }
}