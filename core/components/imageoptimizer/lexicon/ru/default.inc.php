<?php

/**
 * @package imageoptimizer
 */

$_lang['imageoptimizer'] = 'ImageOptimizer';
$_lang['imageoptimizer.desc'] = 'Конвертация WebP/AVIF и responsive изображения';
$_lang['imageoptimizer_vuetools_required'] = 'Для ImageOptimizer нужен VueTools 1.1.2+. Установите через Менеджер пакетов.';

$_lang['imageoptimizer.tab.dashboard'] = 'Обзор';
$_lang['imageoptimizer.tab.queue'] = 'Очередь';
$_lang['imageoptimizer.tab.settings'] = 'Настройки';
$_lang['imageoptimizer.tab.server'] = 'Сервер';
$_lang['imageoptimizer.tab.compatibility'] = 'Совместимость';

$_lang['imageoptimizer.dashboard.title'] = 'Сводка очереди';
$_lang['imageoptimizer.dashboard.progress'] = 'Выполнено';
$_lang['imageoptimizer.dashboard.readiness'] = 'Готовность сервера';
$_lang['imageoptimizer.dashboard.reset_stuck_done'] = 'Сброшено %s зависших задач';

$_lang['imageoptimizer.status.pending'] = 'В очереди';
$_lang['imageoptimizer.status.processing'] = 'Обработка';
$_lang['imageoptimizer.status.done'] = 'Готово';
$_lang['imageoptimizer.status.failed'] = 'Ошибка';
$_lang['imageoptimizer.status.skipped'] = 'Пропуск';

$_lang['imageoptimizer.queue.empty'] = 'Очередь пуста';
$_lang['imageoptimizer.queue.empty_detail'] = 'Загрузите изображения или запустите пересборку очереди.';
$_lang['imageoptimizer.queue.rebuild'] = 'Пересобрать очередь';
$_lang['imageoptimizer.queue.clear'] = 'Очистить варианты';
$_lang['imageoptimizer.queue.retry'] = 'Повторить выбранные';
$_lang['imageoptimizer.queue.reset_stuck'] = 'Сбросить зависшие';
$_lang['imageoptimizer.queue.process'] = 'Обработать очередь';
$_lang['imageoptimizer.queue.process_done'] = 'Обработано %s задач, в очереди осталось %s';
$_lang['imageoptimizer.queue.process_time_budget'] = 'Достигнут лимит времени PHP. Нажмите «Обработать» ещё раз или настройте cron.';
$_lang['imageoptimizer.queue.rebuild_done'] = 'Добавлено %s задач';
$_lang['imageoptimizer.queue.clear_done'] = 'Удалено %s вариантов';
$_lang['imageoptimizer.queue.retry_done'] = 'Обновлено %s задач';
$_lang['imageoptimizer.queue.preview_count'] = 'Будет добавлено %s задач';
$_lang['imageoptimizer.queue.clear_preview'] = 'Будет удалено %s файлов вариантов';
$_lang['imageoptimizer.queue.clear_confirm'] = 'Удалить файлы вариантов и строки очереди? Действие необратимо.';
$_lang['imageoptimizer.queue.path_optional'] = 'Путь к файлу или папке относительно источника (пусто = весь источник)';
$_lang['imageoptimizer.queue.source_all'] = '0 = все источники в очереди';

$_lang['imageoptimizer.col.id'] = 'ID';
$_lang['imageoptimizer.col.source'] = 'Источник';
$_lang['imageoptimizer.col.path'] = 'Путь';
$_lang['imageoptimizer.col.format'] = 'Формат';
$_lang['imageoptimizer.col.width'] = 'Ширина';
$_lang['imageoptimizer.col.status'] = 'Статус';
$_lang['imageoptimizer.col.sizes'] = 'Размеры';
$_lang['imageoptimizer.col.error'] = 'Ошибка';

$_lang['imageoptimizer.filter.all_statuses'] = 'Все статусы';
$_lang['imageoptimizer.filter.status'] = 'Статус';
$_lang['imageoptimizer.filter.search'] = 'Поиск';
$_lang['imageoptimizer.filter.clear'] = 'Сбросить фильтры';

$_lang['imageoptimizer.live.on'] = 'Live';
$_lang['imageoptimizer.live.off'] = 'Пауза';

$_lang['imageoptimizer.save'] = 'Сохранить';
$_lang['imageoptimizer.preview'] = 'Превью';
$_lang['imageoptimizer.run'] = 'Запустить';
$_lang['imageoptimizer.dry_run'] = 'Только просмотр';

$_lang['imageoptimizer.settings.saved'] = 'Настройки сохранены';
$_lang['imageoptimizer.settings.tab.general'] = 'Общие';
$_lang['imageoptimizer.settings.tab.formats'] = 'Форматы';
$_lang['imageoptimizer.settings.tab.frontend'] = 'Фронтенд';
$_lang['imageoptimizer.settings.tab.processing'] = 'Обработка';

$_lang['imageoptimizer.server.title'] = 'Проверка энкодеров';
$_lang['imageoptimizer.server.available'] = 'Доступен';
$_lang['imageoptimizer.server.missing'] = 'Не найден';
$_lang['imageoptimizer.server.cron'] = 'Cron-команда';
$_lang['imageoptimizer.server.cron_copied'] = 'Команда скопирована';

$_lang['imageoptimizer.compat.title'] = 'Установленные пакеты';
$_lang['imageoptimizer.compat.thumb3x'] = 'Thumb3x';
$_lang['imageoptimizer.compat.pthumb'] = 'pThumb / phpThumbOf';
$_lang['imageoptimizer.compat.minishop3'] = 'MiniShop3';
$_lang['imageoptimizer.compat.vuetools'] = 'VueTools';
$_lang['imageoptimizer.compat.installed'] = 'Установлен';
$_lang['imageoptimizer.compat.not_installed'] = 'Не установлен';
$_lang['imageoptimizer.compat.hint'] = 'ImageOptimizer пропускает URL Thumb3x через skip_src_pattern.';
$_lang['imageoptimizer.compat.hint.thumb3x'] = 'URL с thumb3x пропускаются через skip_src_pattern (on-the-fly превью).';
$_lang['imageoptimizer.compat.hint.pthumb'] = 'pThumb не конфликтует: варианты ImageOptimizer лежат рядом с оригиналом.';
$_lang['imageoptimizer.compat.hint.minishop3'] = 'Инъекция <picture> на витрине без правки чанков товаров.';
$_lang['imageoptimizer.compat.hint.vuetools'] = 'VueTools 1.1.2+ обязателен для этой админки.';

$_lang['imageoptimizer.notify.success'] = 'Успех';
$_lang['imageoptimizer.notify.error'] = 'Ошибка';

$_lang['imageoptimizer.yes'] = 'Да';
$_lang['imageoptimizer.no'] = 'Нет';

$_lang['imageoptimizer.error.media_source_forbidden'] = 'Нет доступа к источнику файлов. Проверьте ID источника и право file_list.';
$_lang['imageoptimizer.error.permission_denied'] = 'Недостаточно прав для этого действия.';
$_lang['imageoptimizer.error.unauthorized'] = 'Сессия менеджера истекла. Обновите страницу.';
$_lang['imageoptimizer.error.connector_missing'] = 'Не задан URL коннектора. Обновите страницу менеджера.';
$_lang['imageoptimizer.error.invalid_response'] = 'Коннектор вернул не JSON. Обновите страницу или проверьте лог PHP.';
$_lang['imageoptimizer.error.request_failed'] = 'Запрос не выполнен.';
$_lang['imageoptimizer.error.invalid_status'] = 'Недопустимый статус очереди.';
$_lang['imageoptimizer.error.ids_required'] = 'Выберите хотя бы одну задачу.';
$_lang['imageoptimizer.error.invalid_path'] = 'Недопустимый путь к файлу.';
$_lang['imageoptimizer.error.worker_busy'] = 'Обработка уже выполняется (cron или другой запрос).';
