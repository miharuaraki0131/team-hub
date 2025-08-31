<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateKnowledgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $knowledge = $this->route('knowledge');

        // ログインしていない場合は認証エラー
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        // 投稿者本人または管理者の場合は認証OK
        return $user->id === $knowledge->user_id || $user->is_admin;
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
