{{-- resources/views/events/index.blade.php --}}
<x-portal-layout>
    <style>
        /*
        * FullCalendarの土曜日・日曜日の色をカスタマイズ（月曜日始まり対応）
        */

        /* === ヘッダー（曜日名）の色設定 === */
        /* 6番目 = 土曜日（月曜日が1番目の場合） */
        .fc .fc-col-header-cell:nth-child(6) .fc-col-header-cell-cushion {
            color: #3b82f6 !important;
            /* 青色 */
            font-weight: bold;
        }

        /* 7番目 = 日曜日（月曜日が1番目の場合） */
        .fc .fc-col-header-cell:nth-child(7) .fc-col-header-cell-cushion {
            color: #ef4444 !important;
            /* 赤色 */
            font-weight: bold;
        }

        /* === 日付の数字の色設定 === */
        /* 土曜日の日付 */
        .fc .fc-daygrid-day.fc-day-sat .fc-daygrid-day-number {
            color: #3b82f6 !important;
            /* 青色 */
            font-weight: bold;
        }

        /* 日曜日の日付 */
        .fc .fc-daygrid-day.fc-day-sun .fc-daygrid-day-number {
            color: #ef4444 !important;
            /* 赤色 */
            font-weight: bold;
        }

        /* === 今日の日付の強調表示 === */
        .fc .fc-day-today {
            background-color: #fef9c3 !important;
            /* 薄い黄色 */
        }

        /* === ホバー効果の改善 === */
        .fc .fc-daygrid-day:hover {
            background-color: #f3f4f6 !important;
            cursor: pointer;
        }

        /* === イベント（予定）の見た目調整 === */
        .fc .fc-event {
            border-radius: 6px;
            border: none !important;
            padding: 2px 4px;
            font-size: 12px;
        }

        .fc .fc-event:hover {
            opacity: 0.8;
            cursor: pointer;
        }
    </style>

    {{-- メインコンテンツ --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        {{-- カードヘッダー --}}
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row justify-between items-center">
                {{-- タイトル --}}
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">📅 チームカレンダー</h1>
                    <p class="text-md text-gray-600 mt-1">チーム全体の予定を共有しましょう</p>
                </div>

                {{-- アクションボタン --}}
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('events.create') }}"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold  transition-all duration-200 shadow-md hover:shadow-lg">
                        <span class="mr-2 text-lg">➕</span>
                        <span>新規作成</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- カレンダー本体 --}}
        <div class="p-4 sm:p-8">
            {{-- ▼▼▼ 変更箇所 ▼▼▼ --}}
            <div style="max-width: 80%; margin: 0 auto;">
                <div id="calendar"></div>
            </div>
            {{-- ▲▲▲ 変更箇所 ▲▲▲ --}}
        </div>
    </div>

    {{-- JavaScript --}}
    <script>
        // DOM（ページのHTML）の読み込みが完了したら、中のコードを実行するおまじない
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            // FullCalendarの魔法使いを召喚し、設定を渡す
            var calendar = new window.FullCalendar.Calendar(calendarEl, {
                // --- 使うプラグイン（魔法の種類）を指定 ---
                plugins: [
                    window.FullCalendar.dayGridPlugin, // 月表示カレンダーの魔法
                    window.FullCalendar.interactionPlugin // クリックなどの操作の魔法
                ],

                // --- カレンダーの基本設定 ---
                initialView: 'dayGridMonth', // 最初に表示するビュー（月表示）
                locale: 'ja', // 表示を日本語にする
                firstDay: 1, // 週の初めを月曜日に設定
                weekends: true, // 土日を表示する

                // --- ヘッダーのボタン設定 ---
                headerToolbar: {
                    left: 'prev,next today', // 左側：前月、次月、今日ボタン
                    center: 'title', // 中央：タイトル（例：2025年 9月）
                    right: 'dayGridMonth,dayGridWeek' // 右側：月表示、週表示の切り替えボタン
                },

                // --- イベント（予定）に関する設定 ---
                events: '{{ route('events.json') }}',

                // イベントの見た目をカスタマイズする
                eventContent: function(arg) {
                    let userName = arg.event.extendedProps.user_name || '';
                    let title = arg.event.title;
                    // HTMLを組み立てて返す
                    return {
                        html: `<div class="p-1 text-xs font-bold overflow-hidden whitespace-nowrap">
                                   <span class="font-normal">[${userName}]</span> ${title}
                               </div>`
                    };
                },
                eventTextColor: '#111827', // イベントの文字色を、濃いグレー（ほぼ黒）に固定する

                // --- 操作（インタラクション）に関する設定 ---

                editable: true,
                eventDrop: function(info) {
                    // バックエンドに更新を通知する関数を呼び出す
                    updateEvent(info.event, info.oldEvent, info.revert);
                },
                eventResize: function(info) {
                    // eventDropと、全く同じ関数を呼び出す
                    updateEvent(info.event, info.oldEvent, info.revert);
                },
                dateClick: function(info) {
                    // クエリパラメータを付けて、作成ページにジャンプ！
                    window.location.href = `/events/create?date=${info.dateStr}`;
                },

                eventClick: function(info) {
                    // イベントのIDを使って、詳細ページにジャンプ！
                    window.location.href = `/events/${info.event.id}`;
                }
            });
            calendar.render();
        });

        function updateEvent(event, oldEvent, revertFunc) {
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.error('CSRF token not found!');
                revertFunc();
                return;
            }

            // タイムゾーンの問題を避けるため、日付文字列を直接取得
            const startDate = event.allDay ?
                event.start.getFullYear() + '-' +
                String(event.start.getMonth() + 1).padStart(2, '0') + '-' +
                String(event.start.getDate()).padStart(2, '0') :
                event.start.toISOString().slice(0, 10);

            const endDate = event.end ? new Date(event.end) : new Date(event.start);
            const endDateString = event.allDay ?
                endDate.getFullYear() + '-' +
                String(endDate.getMonth() + 1).padStart(2, '0') + '-' +
                String(endDate.getDate()).padStart(2, '0') :
                endDate.toISOString().slice(0, 10);

            const data = {
                _method: 'PUT',
                title: event.title,
                is_all_day: event.allDay,

                start_date: startDate,
                start_time: event.allDay ? null : event.start.toTimeString().slice(0, 5),

                end_date: endDateString,
                end_time: event.allDay ? null : (event.end ? event.end.toTimeString().slice(0, 5) : event.start
                    .toTimeString().slice(0, 5)),

                visibility: event.extendedProps.visibility || 'public',
                color: event.backgroundColor || '#bfdbfe',
                category: event.extendedProps.category || null,
                body: event.extendedProps.body || null,
            };

            console.log('Original event.start:', event.start);
            console.log('Original event.end:', event.end);
            console.log('Final start_date:', startDate);
            console.log('Final end_date:', endDateString);

            console.log('Event data being sent:', data);

            fetch(`/events/${event.id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (response.status === 403) {
                        alert("この予定を編集する権限がありません。");
                        revertFunc();
                    } else if (!response.ok) {
                        alert("エラーが発生しました。変更を元に戻します。");
                        revertFunc();
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Success:', data);
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert("通信エラーが発生しました。変更を元に戻します。");
                    revertFunc();
                });
        }
    </script>
</x-portal-layout>
