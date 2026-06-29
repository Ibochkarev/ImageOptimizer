<?php

/**
 * @package imageoptimizer
 */

$_lang['setting_imageoptimizer_enabled'] = 'Включено';
$_lang['setting_imageoptimizer_enabled_desc'] = 'Включить обработку ImageOptimizer.';

$_lang['setting_imageoptimizer_formats'] = 'Форматы вывода';
$_lang['setting_imageoptimizer_formats_desc'] = 'Список через запятую (webp, avif).';

$_lang['setting_imageoptimizer_avif_enabled'] = 'Кодирование AVIF';
$_lang['setting_imageoptimizer_avif_enabled_desc'] = 'Создавать AVIF-варианты, если доступны энкодеры.';

$_lang['setting_imageoptimizer_quality'] = 'Качество по умолчанию';
$_lang['setting_imageoptimizer_quality_desc'] = 'Fallback качества (1–100), если не задано quality_jpeg/quality_png.';

$_lang['setting_imageoptimizer_quality_jpeg'] = 'Качество JPEG';
$_lang['setting_imageoptimizer_quality_jpeg_desc'] = 'Качество для JPEG-источников (1–100).';

$_lang['setting_imageoptimizer_quality_png'] = 'Качество PNG';
$_lang['setting_imageoptimizer_quality_png_desc'] = 'Качество для PNG-источников (1–100).';

$_lang['setting_imageoptimizer_method_priority'] = 'Приоритет энкодеров';
$_lang['setting_imageoptimizer_method_priority_desc'] = 'Порядок: cwebp, avifenc, gd, imagick.';

$_lang['setting_imageoptimizer_cron_limit'] = 'Размер пакета cron';
$_lang['setting_imageoptimizer_cron_limit_desc'] = 'Максимум задач очереди на один запуск cron.';

$_lang['setting_imageoptimizer_retention_days'] = 'Срок хранения очереди (дни)';
$_lang['setting_imageoptimizer_retention_days_desc'] = 'Удалять завершённые строки очереди старше N дней.';

$_lang['setting_imageoptimizer_inject_frontend'] = 'Авто <picture> на фронте';
$_lang['setting_imageoptimizer_inject_frontend_desc'] = 'Оборачивать <img> в <picture> с WebP/AVIF.';

$_lang['setting_imageoptimizer_default_sizes'] = 'Атрибут sizes по умолчанию';
$_lang['setting_imageoptimizer_default_sizes_desc'] = 'CSS sizes для responsive, если не задан на <img>.';

$_lang['setting_imageoptimizer_skip_src_pattern'] = 'Паттерн пропуска URL';
$_lang['setting_imageoptimizer_skip_src_pattern_desc'] = 'Подстрока в src для пропуска (например thumb3x).';

$_lang['setting_imageoptimizer_skip_classes'] = 'CSS-классы для пропуска';
$_lang['setting_imageoptimizer_skip_classes_desc'] = 'Классы <img> через запятую.';

$_lang['setting_imageoptimizer_convert_on_upload'] = 'Конвертация при загрузке';
$_lang['setting_imageoptimizer_convert_on_upload_desc'] = 'Добавлять в очередь или синхронно конвертировать при загрузке в File Manager.';

$_lang['setting_imageoptimizer_breakpoints'] = 'Брейкпоинты';
$_lang['setting_imageoptimizer_breakpoints_desc'] = 'Ширины responsive (CSV). Пусто = только оригинал. По умолчанию: 480,768,1024,1440,1920.';

$_lang['setting_imageoptimizer_variant_pattern'] = 'Шаблон имени варианта';
$_lang['setting_imageoptimizer_variant_pattern_desc'] = 'Плейсхолдеры: {basename}, {width}, {ext}. Ширина 0 → image.jpg.webp.';

$_lang['setting_imageoptimizer_upscale'] = 'Апскейл брейкпоинтов';
$_lang['setting_imageoptimizer_upscale_desc'] = 'Создавать варианты шире оригинала. По умолчанию — пропускать (upscale_skip).';

$_lang['setting_imageoptimizer_responsive_min_width'] = 'Мин. ширина responsive';
$_lang['setting_imageoptimizer_responsive_min_width_desc'] = 'Брейкпоинты уже этой ширины (px) не генерируются.';

$_lang['setting_imageoptimizer_inject_email'] = 'Инъекция в email';
$_lang['setting_imageoptimizer_inject_email_desc'] = 'Оборачивать <img> в <picture> в HTML писем (если поддерживается контекст).';

$_lang['setting_imageoptimizer_respect_existing_srcset'] = 'Не трогать srcset';
$_lang['setting_imageoptimizer_respect_existing_srcset_desc'] = 'Пропускать <img>, у которых уже есть атрибут srcset.';

$_lang['setting_imageoptimizer_respect_existing_picture'] = 'Не трогать picture';
$_lang['setting_imageoptimizer_respect_existing_picture_desc'] = 'Пропускать <img> внутри существующего <picture>.';

$_lang['setting_imageoptimizer_respect_existing_loading'] = 'Не трогать loading';
$_lang['setting_imageoptimizer_respect_existing_loading_desc'] = 'Не перезаписывать атрибут loading на <img>.';

$_lang['setting_imageoptimizer_html_cache'] = 'Кэш HTML-инъекции';
$_lang['setting_imageoptimizer_html_cache_desc'] = 'Кэшировать результат <picture> в core/cache/imageoptimizer/html/.';

$_lang['setting_imageoptimizer_convert_on_upload_sync_timeout'] = 'Таймаут sync при загрузке (сек)';
$_lang['setting_imageoptimizer_convert_on_upload_sync_timeout_desc'] = 'Сколько секунд ждать синхронной конвертации при upload в File Manager.';

$_lang['setting_imageoptimizer_reencode_if_unchanged'] = 'Перекодировать без изменений';
$_lang['setting_imageoptimizer_reencode_if_unchanged_desc'] = 'Повторно ставить в очередь done-задачи при том же исходнике.';

$_lang['setting_imageoptimizer_preserve_exif'] = 'Сохранять EXIF';
$_lang['setting_imageoptimizer_preserve_exif_desc'] = 'Передавать EXIF в WebP/AVIF (если энкодер поддерживает).';

$_lang['setting_imageoptimizer_preserve_icc'] = 'Сохранять ICC-профиль';
$_lang['setting_imageoptimizer_preserve_icc_desc'] = 'Встраивать цветовой профиль в выходные файлы.';

$_lang['setting_imageoptimizer_max_html_size'] = 'Макс. размер HTML (байт)';
$_lang['setting_imageoptimizer_max_html_size_desc'] = 'Не обрабатывать HTML больше N байт (защита от OOM). По умолчанию 1048576.';

$_lang['setting_imageoptimizer_skip_lazy_first_images'] = 'Пропуск lazy для первых N img';
$_lang['setting_imageoptimizer_skip_lazy_first_images_desc'] = 'Первые N <img> на странице получают loading=eager вместо lazy.';

$_lang['setting_imageoptimizer_cleanup_on_uninstall'] = 'Очистка при удалении';
$_lang['setting_imageoptimizer_cleanup_on_uninstall_desc'] = 'Удалить варианты, таблицу очереди и кэш при uninstall пакета.';

$_lang['setting_imageoptimizer_disk_warn_gb'] = 'Порог предупреждения диска (ГБ)';
$_lang['setting_imageoptimizer_disk_warn_gb_desc'] = 'Показывать предупреждение в админке, если свободно меньше N ГБ.';

$_lang['setting_imageoptimizer_stuck_minutes'] = 'Таймаут зависших (минуты)';
$_lang['setting_imageoptimizer_stuck_minutes_desc'] = 'Сбрасывать processing дольше N минут.';

$_lang['setting_imageoptimizer_max_memory_limit'] = 'Лимит памяти';
$_lang['setting_imageoptimizer_max_memory_limit_desc'] = 'Поднятие memory_limit для больших файлов (например 512M).';
