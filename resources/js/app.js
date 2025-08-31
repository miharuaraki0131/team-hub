import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// FullCalendarの魔法使いたちを、node_modulesの箱からインポートする
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

// [重要] Bladeテンプレートの中から、いつでも呼び出せるように、グローバルな魔法として登録する
window.FullCalendar = {
    Calendar,
    dayGridPlugin,
    interactionPlugin
};
