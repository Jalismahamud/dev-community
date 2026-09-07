<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->route('comment')?->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return ['body' => ['required', 'string', 'max:10000']];
    }
}
