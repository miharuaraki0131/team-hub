<x-portal-layout>
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        {{-- ヘッダー --}}
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h1 class="text-3xl font-bold text-gray-800">🔔 通知一覧</h1>
            <p class="text-md text-gray-600 mt-1">あなた宛の通知履歴です。</p>
        </div>

        {{-- 通知リスト --}}
        <div class="divide-y divide-gray-200">
            @forelse ($notifications as $notification)
                @php
                    // dataから必要な情報を取得
                    $message = '';
                    $link = '#';
                    $fromUserName = $notification->data['from_user_name'] ?? 'システム';

                    if ($notification->type === 'task_assigned') {
                        $taskTitle = $notification->data['task_title'] ?? '無題のタスク';
                        $message = "{$fromUserName}さんがあなたにタスク「{$taskTitle}」を割り当てました。";
                        if (isset($notification->data['project_id'])) {
                            $link = route('tasks.index', $notification->data['project_id']);
                        }
                    }
                    // ★将来的に他の通知タイプが増えたら、ここに else if を追加
                @endphp

                <a href="{{ $link }}" class="block p-6 hover:bg-gray-50 transition">
                    <div class="flex justify-between items-start">
                        <p class="text-gray-800">{{ $message }}</p>

                        {{-- 未読マーク --}}
                        @if (is_null($notification->read_at))
                            <span class="flex-shrink-0 ml-4 w-3 h-3 bg-blue-500 rounded-full"></span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        {{ $notification->created_at->diffForHumans() }}
                    </p>
                </a>
            @empty
                <div class="p-12 text-center text-gray-500">
                    <p>通知はまだありません。</p>
                </div>
            @endforelse
        </div>

        {{-- ページネーションリンク --}}
        <div class="p-6 bg-gray-50 border-t border-gray-200">
            {{ $notifications->links() }}
        </div>
    </div>
</x-portal-layout>
