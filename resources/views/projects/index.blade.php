{{-- resources/views/projects/index.blade.php --}}

<x-portal-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">プロジェクト一覧</h1>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <ul>
                        @forelse ($projects as $project)
                            <li class="mb-4 pb-4 border-b">
                                <a href="{{ route('projects.show', $project) }}" class="text-xl font-semibold text-blue-600 hover:underline">
                                    {{ $project->name }}
                                </a>
                                <p class="text-gray-600 mt-2">{{ $project->description }}</p>
                            </li>
                        @empty
                            <p>プロジェクトはまだありません。</p>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-portal-layout>
