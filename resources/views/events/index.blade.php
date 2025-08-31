{{-- resources/views/events/index.blade.php --}}
<x-portal-layout>

    {{-- 各ページ固有のスタイルを読み込むための場所（今回は使いませんが、将来のために） --}}
    @push('styles')
        <style>
            /* FullCalendarの見た目を微調整したい場合は、ここにCSSを書けます */
            /* 例：土曜日の色を変える */
            .fc-day-sat {
                color: blue;
            }

            /* 例：日曜日の色を変える */
            .fc-day-sun {
                color: red;
            }
        </style>
    @endpush


    {{-- メインコンテンツ --}}
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
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
                <div id="calendar"></div>
            </div>
        </div>


        {{-- 各ページ固有のJavaScriptを読み込むための場所 --}}
        @push('scripts')
            <script>
                // DOM（ページのHTML）の読み込みが完了したら、中のコードを実行するおまじない
                document.addEventListener('DOMContentLoaded', function() {
                    // HTMLの中から、id='calendar'の要素を探してきて、変数に格納する
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

                        // --- ヘッダーのボタン設定 ---
                        headerToolbar: {
                            left: 'prev,next today', // 左側：前月、次月、今日ボタン
                            center: 'title', // 中央：タイトル（例：2025年 9月）
                            right: 'dayGridMonth,dayGridWeek' // 右側：月表示、週表示の切り替えボタン
                        },

                        // --- イベント（予定）に関する設定 ---

                        // [最重要] どこから予定データを取ってくるかを指定
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

                        // [あなたのアイデア] 日付がクリックされた時の処理
                        dateClick: function(info) {
                            // クエリパラメータを付けて、作成ページにジャンプ！
                            window.location.href = `/events/create?date=${info.dateStr}`;
                        },

                        // [あなたのアイデア] イベント（予定）がクリックされた時の処理
                        eventClick: function(info) {
                            // イベントのIDを使って、詳細ページにジャンプ！
                            window.location.href = `/events/${info.event.id}`;
                        }
                    });

                    // 全ての設定が終わったら、カレンダーを描画する！
                    calendar.render();
                });
            </script>
        @endpush

</x-portal-layout>
