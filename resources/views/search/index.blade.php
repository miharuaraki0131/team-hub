<x-portal-layout>
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        {{-- ヘッダー --}}
        <div class="p-6 bg-gray-50 border-b border-gray-200">
            <h1 class="text-3xl font-bold text-gray-800">🔍 検索結果</h1>
            @if (!empty($keyword))
                <p class="text-md text-gray-600 mt-2">
                    「<span class="font-bold text-blue-600">{{ $keyword }}</span>」の検索結果
                </p>
            @endif
        </div>

        {{-- Knowledgeの検索結果 --}}
        <div class="p-6">
            <h2 class="text-2xl font-bold text-gray-700 mb-4">共有事項 ({{ $knowledgeResults->total() }}件)</h2>
            <div class="divide-y divide-gray-200">
                @forelse ($knowledgeResults as $result)
                    <div class="py-4">
                        <a href="{{ route('knowledges.show', $result) }}" class="block group">
                            <h3 class="text-xl font-bold text-blue-700 group-hover:underline">
                                {{-- ハイライトされたタイトルを表示（<mark>タグのみを許可） --}}
                                {!! isset($result->_formatted['title'])
                                    ? strip_tags($result->_formatted['title'], '<mark>')
                                    : e($result->title) !!}
                            </h3>
                            <p class="mt-2 text-gray-600 text-sm leading-relaxed">
                                {{-- ハイライトされた本文を表示（<mark>タグのみを許可、文字数制限） --}}
                                @if(isset($result->_formatted['body']))
                                    {!! Str::limit(strip_tags($result->_formatted['body'], '<mark>'), 150) !!}
                                @else
                                    {{ Str::limit(strip_tags($result->body), 150) }}
                                @endif
                            </p>
                        </a>
                    </div>
                @empty
                    <p class="text-gray-500">一致する共有事項はありませんでした。</p>
                @endforelse
            </div>
            @if ($knowledgeResults->hasPages())
                <div class="mt-4">{{ $knowledgeResults->appends(['keyword' => $keyword])->links() }}</div>
            @endif
        </div>

        {{-- Taskの検索結果 --}}
        <div class="p-6 border-t border-gray-200">
            <h2 class="text-2xl font-bold text-gray-700 mb-4">タスク ({{ $taskResults->total() }}件)</h2>
            <div class="divide-y divide-gray-200">
                @forelse ($taskResults as $result)
                    <div class="py-4">
                        <a href="{{ route('tasks.index', $result->project_id) }}" class="block group">
                            <h3 class="text-xl font-bold text-blue-700 group-hover:underline">
                                {{-- ハイライトされたタイトルを表示（<mark>タグのみを許可） --}}
                                {!! isset($result->_formatted['title'])
                                    ? strip_tags($result->_formatted['title'], '<mark>')
                                    : e($result->title) !!}
                            </h3>
                            <span class="text-sm text-gray-500">(プロジェクト: {{ $result->project->name }})</span>
                            <p class="mt-2 text-gray-600 text-sm leading-relaxed">
                                {{-- ハイライトされた説明を表示（<mark>タグのみを許可、文字数制限） --}}
                                @if(isset($result->_formatted['description']))
                                    {!! Str::limit(strip_tags($result->_formatted['description'], '<mark>'), 150) !!}
                                @else
                                    {{ Str::limit(strip_tags($result->description), 150) }}
                                @endif
                            </p>
                        </a>
                    </div>
                @empty
                    <p class="text-gray-500">一致するタスクはありませんでした。</p>
                @endforelse
            </div>
            @if ($taskResults->hasPages())
                <div class="mt-4">{{ $taskResults->appends(['keyword' => $keyword])->links() }}</div>
            @endif
        </div>
    </div>
</x-portal-layout>
