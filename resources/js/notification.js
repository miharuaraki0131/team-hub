import { route } from 'ziggy-js';
// --- 必要なHTML要素を取得 ---
const bell = document.getElementById('notification-bell');
const badge = document.getElementById('notification-badge');
const dropdown = document.getElementById('notification-dropdown');
const notificationList = document.getElementById('notification-list');
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ▼▼▼ 修正箇所 ▼▼▼
// bell要素が存在する場合にのみ、全ての処理を実行するようにする
if (bell) {

    // --- 未読件数を取得してバッジを更新する関数 ---
    async function fetchUnreadCount() {
        try {
            const response = await fetch(route("notifications.count"));
            const data = await response.json();

            if (data.count > 0) {
                badge.textContent = data.count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error fetching unread count:', error);
        }
    }

    // --- 通知一覧を取得してドロップダウンに表示する関数 ---
    async function fetchNotifications() {
        notificationList.innerHTML = '<div class="p-4 text-center text-gray-500">読み込み中...</div>';

        try {
            const response = await fetch(route("notifications.index"));
            const notifications = await response.json();

            notificationList.innerHTML = '';

            if (notifications.length === 0) {
                notificationList.innerHTML = '<div class="p-4 text-center text-gray-500">新しい通知はありません。</div>';
                return;
            }

            notifications.forEach(notification => {
                let message = '';
                let link = '#';

                if (notification.type === 'task_assigned') {
                    message = `${notification.data.from_user_name}さんがあなたにタスク「${notification.data.task_title}」を割り当てました。`;
                    if (notification.data.project_id) {
                        link = `/projects/${notification.data.project_id}/tasks`;
                    }
                }

                const item = document.createElement('a');
                item.href = link;
                item.className = `block px-4 py-3 hover:bg-gray-100 border-b ${!notification.read_at ? 'bg-blue-50' : ''}`;
                item.innerHTML = `
                    <p class="text-sm text-gray-800">${message}</p>
                    <p class="text-xs text-gray-500 mt-1">${new Date(notification.created_at).toLocaleString('ja-JP')}</p>
                `;
                notificationList.appendChild(item);
            });

        } catch (error) {
            console.error('Error fetching notifications:', error);
            notificationList.innerHTML = '<div class="p-4 text-center text-red-500">通知の読み込みに失敗しました。</div>';
        }
    }

    // --- 未読通知を既読にする関数 ---
    async function markAsRead() {
        if (badge.classList.contains('hidden')) {
            return;
        }

        try {
            await fetch(route('notifications.markAsRead'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            });
            badge.classList.add('hidden');
            badge.textContent = '0';
        } catch (error) {
            console.error('Error marking notifications as read:', error);
        }
    }


    // --- イベントリスナーの設定 ---
    bell.addEventListener('click', function (e) {
        e.stopPropagation();
        const isHidden = dropdown.classList.toggle('hidden');
        if (!isHidden) {
            fetchNotifications();
            markAsRead();
        }
    });

    document.addEventListener('click', function () {
        if (!dropdown.classList.contains('hidden')) {
            dropdown.classList.add('hidden');
        }
    });

    dropdown.addEventListener('click', function (e) {
        e.stopPropagation();
    });


    // --- 初回読み込み ---
    fetchUnreadCount();

} // ▲▲▲ 修正箇所 (ifブロックの閉じ括弧) ▲▲▲
