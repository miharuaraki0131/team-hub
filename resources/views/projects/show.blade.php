{{-- resources/views/projects/show.blade.php --}}

<x-portal-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800">{{ $project->name }}</h1>
            <p class="text-gray-600 mt-2 mb-8">{{ $project->description }}</p>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-semibold mb-4">タスク一覧 (WBS)</h2>
                    {{-- タスクリストの開始点 --}}
                    @include('projects._task_list', ['tasks' => $tasks])
                </div>
            </div>
        </div>
    </div>
</x-portal-layout>
