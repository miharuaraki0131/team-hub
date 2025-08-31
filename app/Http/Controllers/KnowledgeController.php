<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Knowledge;
use App\Http\Requests\StoreKnowledgeRequest;
use App\Http\Requests\UpdateKnowledgeRequest;

class KnowledgeController extends Controller
{
    /**
     * 共有事項の一覧を表示
     */
    public function index(Request $request)
    {
        $query = Knowledge::with('user')
            ->published() // 公開中のもののみ
            ->orderBy('is_pinned', 'desc') // 固定表示を上に
            ->orderBy('created_at', 'desc'); // 新しいものから

        // カテゴリーでフィルタ（将来の拡張用）
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // 検索機能
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('body', 'like', "%{$searchTerm}%");
            });
        }

        $knowledges = $query->paginate(10);

        return view('knowledges.index', compact('knowledges'));
    }

    /**
     * 共有事項の詳細表示
     */
    public function show(Knowledge $knowledge)
    {
        // 公開状態チェック
        if (!$knowledge->isPublished()) {
            // ログインしていない場合は404
            if (!Auth::check()) {
                abort(404);
            }

            $user = Auth::user();
            // 投稿者本人または管理者以外は見れない
            if ($user->id !== $knowledge->user_id && !$user->is_admin) {
                abort(404);
            }
        }

        // 閲覧数をカウント
        $knowledge->incrementViewCount();

        return view('knowledges.show', compact('knowledge'));
    }

    /**
     * 新規作成フォーム
     */
    public function create()
    {
        return view('knowledges.create');
    }

    /**
     * 新規作成処理
     */
    public function store(StoreKnowledgeRequest $request)
    {
        $knowledge = Knowledge::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'body' => $request->body,
            'is_pinned' => $request->boolean('is_pinned'),
            'published_at' => $request->published_at,
            'expired_at' => $request->expired_at,
            'category' => $request->category,
            'priority' => $request->priority ?? 'normal',
        ]);

        return redirect()->route('knowledges.show', $knowledge)
            ->with('success', '共有事項を投稿しました！');
    }

    /**
     * 編集フォーム
     */
    public function edit(Knowledge $knowledge)
    {
        // 権限チェック: 投稿者本人または管理者のみ
        $user = Auth::user();
        if ($user->id !== $knowledge->user_id && !$user->is_admin) {
            abort(403);
        }

        return view('knowledges.edit', compact('knowledge'));
    }

    /**
     * 更新処理
     */
    public function update(UpdateKnowledgeRequest $request, Knowledge $knowledge)
    {
        // 権限チェック: 投稿者本人または管理者のみ
        $user = Auth::user();
        if ($user->id !== $knowledge->user_id && !$user->is_admin) {
            abort(403);
        }

        $knowledge->update([
            'title' => $request->title,
            'body' => $request->body,
            'is_pinned' => $request->boolean('is_pinned'),
            'published_at' => $request->published_at,
            'expired_at' => $request->expired_at,
            'category' => $request->category,
            'priority' => $request->priority ?? 'normal',
        ]);

        return redirect()->route('knowledges.show', $knowledge)
            ->with('success', '共有事項を更新しました！');
    }

    /**
     * 削除処理（論理削除）
     */
    public function destroy(Knowledge $knowledge)
    {
        // 権限チェック: 投稿者本人または管理者のみ
        $user = Auth::user();
        if ($user->id !== $knowledge->user_id && !$user->is_admin) {
            abort(403);
        }

        $knowledge->delete(); // 論理削除

        return redirect()->route('knowledges.index')
            ->with('success', '共有事項を削除しました。');
    }

    /**
     * ダッシュボード用の最新共有事項を取得
     */
    public function getLatestForDashboard(int $limit = 5)
    {
        return Knowledge::with('user')
            ->published()
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
