<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ログインしているユーザーなら、誰でも作成できる
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'start_time' => ['required_unless:is_all_day,true', 'nullable', 'date_format:H:i'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'end_time' => ['required_unless:is_all_day,true', 'nullable', 'date_format:H:i'],
            'is_all_day' => ['boolean'],
            'category' => ['nullable', 'string', 'max:100'],
            'visibility' => ['required', 'in:public,private'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],

            // --- [ここがポイント！] ---
            // 終了時刻が、開始時刻より後であるかをチェックする、カスタムルール
            'end_time' => [
                // ... (他のルール)
                function ($attribute, $value, $fail) {
                    // is_all_day が true の場合は、このルールをスキップ
                    if ($this->input('is_all_day')) {
                        return;
                    }

                    $startDate = $this->input('start_date');
                    $endDate = $this->input('end_date');
                    $startTime = $this->input('start_time');
                    $endTime = $value; // $value は、この'end_time'自身の値

                    if ($startDate && $endDate && $startTime && $endTime) {
                        $startDatetime = \Carbon\Carbon::parse($startDate . ' ' . $startTime);
                        $endDatetime = \Carbon\Carbon::parse($endDate . ' ' . $endTime);

                        if ($endDatetime <= $startDatetime) {
                            $fail('終了日時は、開始日時より後に設定してください。');
                        }
                    }
                },
            ],
        ];
    }
}
