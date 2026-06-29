<?php

/**
 * @package imageoptimizer
 */

$_lang['imageoptimizer'] = 'ImageOptimizer';
$_lang['imageoptimizer.desc'] = 'Конвертація WebP/AVIF та responsive зображення';
$_lang['imageoptimizer_vuetools_required'] = 'Для ImageOptimizer потрібен VueTools 1.1.2+. Встановіть через Менеджер пакетів.';

$_lang['imageoptimizer.tab.dashboard'] = 'Огляд';
$_lang['imageoptimizer.tab.queue'] = 'Черга';
$_lang['imageoptimizer.tab.settings'] = 'Налаштування';
$_lang['imageoptimizer.tab.server'] = 'Сервер';
$_lang['imageoptimizer.tab.compatibility'] = 'Сумісність';

$_lang['imageoptimizer.dashboard.title'] = 'Огляд черги';
$_lang['imageoptimizer.dashboard.progress'] = 'Завершено';
$_lang['imageoptimizer.dashboard.readiness'] = 'Готовність сервера';
$_lang['imageoptimizer.dashboard.reset_stuck_done'] = 'Скинуто %s завислих';

$_lang['imageoptimizer.status.pending'] = 'Очікує';
$_lang['imageoptimizer.status.processing'] = 'Обробка';
$_lang['imageoptimizer.status.done'] = 'Готово';
$_lang['imageoptimizer.status.failed'] = 'Помилка';
$_lang['imageoptimizer.status.skipped'] = 'Пропущено';

$_lang['imageoptimizer.queue.empty'] = 'Черга порожня';
$_lang['imageoptimizer.queue.empty_detail'] = 'Завантажте зображення або запустіть пересборку.';
$_lang['imageoptimizer.queue.rebuild'] = 'Пересобрати чергу';
$_lang['imageoptimizer.queue.clear'] = 'Очистити варіанти';
$_lang['imageoptimizer.queue.retry'] = 'Повторити вибрані';
$_lang['imageoptimizer.queue.reset_stuck'] = 'Скинути завислі';
$_lang['imageoptimizer.queue.process'] = 'Обробити чергу';
$_lang['imageoptimizer.queue.process_done'] = 'Оброблено %s задач, у черзі залишилось %s';
$_lang['imageoptimizer.queue.process_time_budget'] = 'Досягнуто ліміт часу PHP. Натисніть «Обробити» ще раз або налаштуйте cron.';
$_lang['imageoptimizer.queue.rebuild_done'] = 'Додано %s задач';
$_lang['imageoptimizer.queue.clear_done'] = 'Видалено %s варіантів';
$_lang['imageoptimizer.queue.retry_done'] = 'Оновлено %s задач';
$_lang['imageoptimizer.queue.preview_count'] = 'Буде додано %s задач';
$_lang['imageoptimizer.queue.clear_preview'] = 'Буде видалено %s файлів варіантів';
$_lang['imageoptimizer.queue.clear_confirm'] = 'Видалити файли варіантів і рядки черги? Дія незворотна.';
$_lang['imageoptimizer.queue.path_optional'] = 'Шлях до файлу або папки відносно джерела (пусто = все джерело)';
$_lang['imageoptimizer.queue.source_all'] = '0 = всі джерела в черзі';

$_lang['imageoptimizer.col.id'] = 'ID';
$_lang['imageoptimizer.col.source'] = 'Джерело';
$_lang['imageoptimizer.col.path'] = 'Шлях';
$_lang['imageoptimizer.col.format'] = 'Формат';
$_lang['imageoptimizer.col.width'] = 'Ширина';
$_lang['imageoptimizer.col.status'] = 'Статус';
$_lang['imageoptimizer.col.sizes'] = 'Розміри';
$_lang['imageoptimizer.col.error'] = 'Помилка';

$_lang['imageoptimizer.filter.all_statuses'] = 'Всі статуси';
$_lang['imageoptimizer.filter.status'] = 'Статус';
$_lang['imageoptimizer.filter.search'] = 'Пошук';
$_lang['imageoptimizer.filter.clear'] = 'Очистити фільтри';

$_lang['imageoptimizer.live.on'] = 'Live';
$_lang['imageoptimizer.live.off'] = 'Пауза';

$_lang['imageoptimizer.save'] = 'Зберегти';
$_lang['imageoptimizer.preview'] = 'Превью';
$_lang['imageoptimizer.run'] = 'Запустити';
$_lang['imageoptimizer.dry_run'] = 'Тільки перегляд';

$_lang['imageoptimizer.settings.saved'] = 'Налаштування збережено';
$_lang['imageoptimizer.settings.tab.general'] = 'Загальні';
$_lang['imageoptimizer.settings.tab.formats'] = 'Формати';
$_lang['imageoptimizer.settings.tab.frontend'] = 'Фронтенд';
$_lang['imageoptimizer.settings.tab.processing'] = 'Обробка';

$_lang['imageoptimizer.server.title'] = 'Перевірка енкодерів';
$_lang['imageoptimizer.server.available'] = 'Доступно';
$_lang['imageoptimizer.server.missing'] = 'Немає';
$_lang['imageoptimizer.server.cron'] = 'Команда cron';
$_lang['imageoptimizer.server.cron_copied'] = 'Команду cron скопійовано';

$_lang['imageoptimizer.compat.title'] = 'Установлені пакети';
$_lang['imageoptimizer.compat.thumb3x'] = 'Thumb3x';
$_lang['imageoptimizer.compat.pthumb'] = 'pThumb / phpThumbOf';
$_lang['imageoptimizer.compat.minishop3'] = 'MiniShop3';
$_lang['imageoptimizer.compat.vuetools'] = 'VueTools';
$_lang['imageoptimizer.compat.installed'] = 'Установлено';
$_lang['imageoptimizer.compat.not_installed'] = 'Не установлено';
$_lang['imageoptimizer.compat.hint'] = 'ImageOptimizer пропускає URL Thumb3x через skip_src_pattern.';
$_lang['imageoptimizer.compat.hint.thumb3x'] = 'URL з thumb3x пропускаються через skip_src_pattern (on-the-fly превʼю).';
$_lang['imageoptimizer.compat.hint.pthumb'] = 'pThumb не конфліктує: варіанти ImageOptimizer лежать поруч з оригіналом.';
$_lang['imageoptimizer.compat.hint.minishop3'] = 'Інʼєкція <picture> на вітрині без правки чанків товарів.';
$_lang['imageoptimizer.compat.hint.vuetools'] = 'VueTools 1.1.2+ обовʼязковий для цієї адмінки.';

$_lang['imageoptimizer.notify.success'] = 'Успіх';
$_lang['imageoptimizer.notify.error'] = 'Помилка';

$_lang['imageoptimizer.yes'] = 'Так';
$_lang['imageoptimizer.no'] = 'Ні';

$_lang['imageoptimizer.error.media_source_forbidden'] = 'Немає доступу до джерела файлів. Перевірте ID джерела та право file_list.';
$_lang['imageoptimizer.error.permission_denied'] = 'Недостатньо прав для цієї дії.';
$_lang['imageoptimizer.error.unauthorized'] = 'Сесію менеджера завершено. Оновіть сторінку.';
$_lang['imageoptimizer.error.invalid_status'] = 'Недопустимий статус черги.';
$_lang['imageoptimizer.error.ids_required'] = 'Оберіть хоча б одну задачу.';
$_lang['imageoptimizer.error.invalid_path'] = 'Недопустимий шлях до файлу.';
$_lang['imageoptimizer.error.worker_busy'] = 'Обробка вже виконується (cron або інший запит).';
