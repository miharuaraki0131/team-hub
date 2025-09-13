<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * ログインユーザーの最新の通知を取得する (未読・既読問わず)
     */
    public function index()
    {
        // ログインユーザーの通知を新しい順に10件取得
        $notifications = Auth::user()->notifications()->latest()->take(10)->get();
        return response()->json($notifications);
    }

    /**
     * ログインユーザーの未読通知件数を取得する
     */
    public function count()
    {
        // 先ほどUserモデルに追加したunreadNotificationsリレーションを活用
        $count = Auth::user()->unreadNotifications()->count();
        return response()->json(['count' => $count]);
    }

    /**
     * ログインユーザーの全ての未読通知を「既読」にする
     */
    public function markAsRead()
    {
        // これもunreadNotificationsリレーションのおかげで、たった1行！
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }



    // ▼▼▼ この新しいメソッドを追加 ▼▼▼
    /**
     * 通知一覧ページを表示する
     */
    public function showNotificationsPage()
    {
        // ログインユーザーの全ての通知を、新しい順にページネーションで取得
        $notifications = Auth::user()->notifications()->latest()->paginate(15);

        // 未読通知を全て既読にする（ページを開いた時点で既読とみなす）
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return view('notifications.index', compact('notifications'));
    }

}
