<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * ユーザーがこのリクエストを行う権限を持っているかどうかを判断する
     */
    public function authorize(): bool
    {
        // [ここが、権限チェックの場所！]
        // ルートから渡された 'event' パラメータを取得
        $event = $this->route('event');

        // ログインしているユーザーが、そのイベントの作成者であるか、
        // もしくは、管理者である場合にのみ、true（許可）を返す
        return $event->user_id === Auth::id() || Auth::user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     * リクエストに適用されるバリデーションルールを取得する
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'start_time' => ['required_unless:is_all_day,true', 'nullable', 'date_format:H:i'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_all_day' => ['sometimes', 'boolean'],
            'category' => ['nullable', 'string', 'max:100'],
            'visibility' => ['required', 'in:public,private'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],

            // [カスタムバリデーションルール]
            'end_time' => [
                'required_unless:is_all_day,true',
                'nullable',
                'date_format:H:i',
                // クロージャ（無名関数）を使って、複雑な条件をチェック
                function ($attribute, $value, $fail) {
                    // is_all_day が true (または 1) の場合は、このチェックをスキップ
                    if ($this->boolean('is_all_day')) {
                        return;
                    }

                    $startDate = $this->input('start_date');
                    $endDate = $this->input('end_date');
                    $startTime = $this->input('start_time');
                    // $value は、この 'end_time' フィールド自身の値
                    $endTime = $value;

                    // 全ての日時データが存在する場合のみ、比較を行う
                    if ($startDate && $endDate && $startTime && $endTime) {
                        try {
                            $startDatetime = \Carbon\Carbon::parse($startDate . ' ' . $startTime);
                            $endDatetime = \Carbon\Carbon::parse($endDate . ' ' . $endTime);

                            if ($endDatetime->lte($startDatetime)) { // lte() = less than or equal to
                                // もし終了日時が開始日時以前なら、バリデーション失敗
                                $fail('終了日時は、開始日時より後の時刻に設定してください。');
                            }
                        } catch (\Exception $e) {
                            // 日付の形式が不正でパースに失敗した場合など
                            $fail('日時の形式が正しくありません。');
                        }
                    }
                },
            ],
        ];
    }
}
