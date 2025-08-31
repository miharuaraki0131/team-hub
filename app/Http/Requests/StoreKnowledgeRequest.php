<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;


class StoreKnowledgeRequest extends FormRequest
{
    public function authorize(): bool
    {
         return Auth::check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'is_pinned' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'expired_at' => ['nullable', 'date', 'after:published_at'],
            'category' => ['nullable', 'string', 'in:announcement,meeting,manual,other'],
            'priority' => ['nullable', 'string', 'in:low,normal,high'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルは必須です。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'body.required' => '本文は必須です。',
            'expired_at.after' => '公開終了日時は公開開始日時より後に設定してください。',
        ];
    }
}
