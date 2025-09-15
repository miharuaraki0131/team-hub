<?php

namespace App\Http\Controllers;

use App\Models\Knowledge;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');

        if (empty($keyword)) {
            $knowledgeResults = new LengthAwarePaginator([], 0, 5, null, ['pageName' => 'knowledge_page']);
            $taskResults = new LengthAwarePaginator([], 0, 5, null, ['pageName' => 'task_page']);
            return view('search.index', compact('keyword', 'knowledgeResults', 'taskResults'));
        }

        // シンプルなScout検索
        $knowledgeModels = Knowledge::search($keyword)->get();
        $taskModels = Task::search($keyword)->get();

        // リレーションを手動でロード
        $knowledgeModels->load('user');
        $taskModels->load('project');

        // PHP側でハイライト処理
        $knowledgeResults = $knowledgeModels->map(function ($model) use ($keyword) {
            $model->highlighted_title = $this->highlightText($model->title, $keyword);
            $model->highlighted_body = $this->highlightText($model->body, $keyword);
            return $model;
        });

        $taskResults = $taskModels->map(function ($model) use ($keyword) {
            $model->highlighted_title = $this->highlightText($model->title, $keyword);
            $model->highlighted_description = $this->highlightText($model->description, $keyword);
            return $model;
        });

        // ページネーション
        $knowledgePaginator = $this->manualPaginate($knowledgeResults, 5, 'knowledge_page', $request);
        $taskPaginator = $this->manualPaginate($taskResults, 5, 'task_page', $request);

        return view('search.index', [
            'keyword' => $keyword,
            'knowledgeResults' => $knowledgePaginator,
            'taskResults' => $taskPaginator,
        ]);
    }

    /**
     * テキスト内のキーワードをハイライトするシンプルな関数
     */
    private function highlightText($text, $keyword)
    {
        if (empty($text) || empty($keyword)) {
            return $text;
        }

        // 大文字小文字を無視してハイライト
        return preg_replace(
            '/(' . preg_quote($keyword, '/') . ')/iu',
            '<mark>$1</mark>',
            $text
        );
    }

    /**
     * コレクションから手動でページネーターを作成
     */
    private function manualPaginate(Collection $items, $perPage, $pageName, Request $request)
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage($pageName);
        $currentItems = $items->slice(($currentPage - 1) * $perPage, $perPage);
        return new LengthAwarePaginator($currentItems, $items->count(), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'pageName' => $pageName,
            'query' => $request->query(),
        ]);
    }
}
