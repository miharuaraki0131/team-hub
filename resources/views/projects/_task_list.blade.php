{{-- resources/views/projects/_task_list.blade.php --}}

<ul class="space-y-4 {{ $isSub ?? false ? 'pl-8 border-l border-gray-200' : '' }}">
    @forelse ($tasks as $task)
        <li class="p-4 bg-gray-50 rounded-lg">
            <div class="font-bold">{{ $task->title }}</div>
            <div class="text-sm text-gray-500">
                <span>担当: {{ $task->user->name ?? '未割り当て' }}</span> |
                <span>ステータス: {{ $task->status }}</span>
            </div>

            {{-- もし子タスクが存在すれば、この部品自身を再度呼び出す（再帰） --}}
            @if ($task->children->isNotEmpty())
                <div class="mt-4">
                    @include('projects._task_list', ['tasks' => $task->children, 'isSub' => true])
                </div>
            @endif
        </li>
    @empty
        <p class="text-gray-500">このレベルのタスクはありません。</p>
    @endforelse
</ul>
