<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreDailyReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // このリクエストを送信できるのは、ログインしているユーザーだけである、と宣言する
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
       return [
        'report_date' => ['required', 'date'], // 日付は必須のまま
        'summary_today' => ['nullable', 'string', 'max:1000'], // 'required' を 'nullable' に変更
        'discrepancy' => ['nullable', 'string', 'max:1000'],   // 'required' を 'nullable' に変更
        'summary_tomorrow' => ['nullable', 'string', 'max:1000'], // 'required' を 'nullable' に変更
        'issues_thoughts' => ['nullable', 'string', 'max:1000'],  // 'required' を 'nullable' に変更
    ];
    }
}
