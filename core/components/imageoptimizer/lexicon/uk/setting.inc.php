<?php

/**
 * @package imageoptimizer
 */

$_lang['setting_imageoptimizer_enabled'] = 'Увімкнено';
$_lang['setting_imageoptimizer_enabled_desc'] = 'Увімкнути обробку ImageOptimizer.';

$_lang['setting_imageoptimizer_formats'] = 'Формати виводу';
$_lang['setting_imageoptimizer_formats_desc'] = 'Список через кому (webp, avif).';

$_lang['setting_imageoptimizer_avif_enabled'] = 'Кодування AVIF';
$_lang['setting_imageoptimizer_avif_enabled_desc'] = 'Створювати AVIF-варіанти, якщо доступні енкодери.';

$_lang['setting_imageoptimizer_quality'] = 'Якість за замовчуванням';
$_lang['setting_imageoptimizer_quality_desc'] = 'Fallback якості (1–100), якщо не задано quality_jpeg/quality_png.';

$_lang['setting_imageoptimizer_quality_jpeg'] = 'Якість JPEG';
$_lang['setting_imageoptimizer_quality_jpeg_desc'] = 'Якість для JPEG-джерел (1–100).';

$_lang['setting_imageoptimizer_quality_png'] = 'Якість PNG';
$_lang['setting_imageoptimizer_quality_png_desc'] = 'Якість для PNG-джерел (1–100).';

$_lang['setting_imageoptimizer_method_priority'] = 'Пріоритет енкодерів';
$_lang['setting_imageoptimizer_method_priority_desc'] = 'Порядок: cwebp, avifenc, gd, imagick.';

$_lang['setting_imageoptimizer_cron_limit'] = 'Розмір пакета cron';
$_lang['setting_imageoptimizer_cron_limit_desc'] = 'Максимум задач черги за один запуск cron.';

$_lang['setting_imageoptimizer_retention_days'] = 'Термін зберігання черги (дні)';
$_lang['setting_imageoptimizer_retention_days_desc'] = 'Видаляти завершені рядки черги старші за N днів.';

$_lang['setting_imageoptimizer_inject_frontend'] = 'Авто <picture> на фронті';
$_lang['setting_imageoptimizer_inject_frontend_desc'] = 'Обгортати <img> у <picture> з WebP/AVIF.';

$_lang['setting_imageoptimizer_default_sizes'] = 'Атрибут sizes за замовчуванням';
$_lang['setting_imageoptimizer_default_sizes_desc'] = 'CSS sizes для responsive, якщо не задано на <img>.';

$_lang['setting_imageoptimizer_skip_src_pattern'] = 'Патерн пропуску URL';
$_lang['setting_imageoptimizer_skip_src_pattern_desc'] = 'Підрядок у src для пропуску (наприклад thumb3x).';

$_lang['setting_imageoptimizer_skip_classes'] = 'CSS-класи для пропуску';
$_lang['setting_imageoptimizer_skip_classes_desc'] = 'Класи <img> через кому.';

$_lang['setting_imageoptimizer_convert_on_upload'] = 'Конвертація при завантаженні';
$_lang['setting_imageoptimizer_convert_on_upload_desc'] = 'Додавати в чергу або синхронно конвертувати при завантаженні в File Manager.';

$_lang['setting_imageoptimizer_breakpoints'] = 'Брейкпоінти';
$_lang['setting_imageoptimizer_breakpoints_desc'] = 'Ширини responsive (CSV). Пусто = лише оригінал. За замовчуванням: 480,768,1024,1440,1920.';

$_lang['setting_imageoptimizer_variant_pattern'] = 'Шаблон імені варіанту';
$_lang['setting_imageoptimizer_variant_pattern_desc'] = 'Плейсхолдери: {basename}, {width}, {ext}. Ширина 0 → image.jpg.webp.';

$_lang['setting_imageoptimizer_upscale'] = 'Апскейл брейкпоінтів';
$_lang['setting_imageoptimizer_upscale_desc'] = 'Створювати варіанти ширші за оригінал. За замовчуванням — пропускати.';

$_lang['setting_imageoptimizer_responsive_min_width'] = 'Мін. ширина responsive';
$_lang['setting_imageoptimizer_responsive_min_width_desc'] = 'Брейкпоінти вужчі за цю ширину (px) не генеруються.';

$_lang['setting_imageoptimizer_inject_email'] = 'Ін\'єкція в email';
$_lang['setting_imageoptimizer_inject_email_desc'] = 'Обгортати <img> у <picture> в HTML листів.';

$_lang['setting_imageoptimizer_respect_existing_srcset'] = 'Не чіпати srcset';
$_lang['setting_imageoptimizer_respect_existing_srcset_desc'] = 'Пропускати <img> з наявним атрибутом srcset.';

$_lang['setting_imageoptimizer_respect_existing_picture'] = 'Не чіпати picture';
$_lang['setting_imageoptimizer_respect_existing_picture_desc'] = 'Пропускати <img> всередині існуючого <picture>.';

$_lang['setting_imageoptimizer_respect_existing_loading'] = 'Не чіпати loading';
$_lang['setting_imageoptimizer_respect_existing_loading_desc'] = 'Не перезаписувати атрибут loading на <img>.';

$_lang['setting_imageoptimizer_html_cache'] = 'Кеш HTML-ін\'єкції';
$_lang['setting_imageoptimizer_html_cache_desc'] = 'Кешувати результат <picture> у core/cache/imageoptimizer/html/.';

$_lang['setting_imageoptimizer_convert_on_upload_sync_timeout'] = 'Таймаут sync при завантаженні (сек)';
$_lang['setting_imageoptimizer_convert_on_upload_sync_timeout_desc'] = 'Скільки секунд чекати синхронної конвертації при upload у File Manager.';

$_lang['setting_imageoptimizer_reencode_if_unchanged'] = 'Перекодувати без змін';
$_lang['setting_imageoptimizer_reencode_if_unchanged_desc'] = 'Повторно ставити в чергу done-задачі для того самого джерела.';

$_lang['setting_imageoptimizer_preserve_exif'] = 'Зберігати EXIF';
$_lang['setting_imageoptimizer_preserve_exif_desc'] = 'Передавати EXIF у WebP/AVIF (якщо енкодер підтримує).';

$_lang['setting_imageoptimizer_preserve_icc'] = 'Зберігати ICC-профіль';
$_lang['setting_imageoptimizer_preserve_icc_desc'] = 'Вбудовувати колірний профіль у вихідні файли.';

$_lang['setting_imageoptimizer_max_html_size'] = 'Макс. розмір HTML (байт)';
$_lang['setting_imageoptimizer_max_html_size_desc'] = 'Не обробляти HTML більший за N байт. За замовчуванням 1048576.';

$_lang['setting_imageoptimizer_skip_lazy_first_images'] = 'Пропуск lazy для перших N img';
$_lang['setting_imageoptimizer_skip_lazy_first_images_desc'] = 'Перші N <img> на сторінці отримують loading=eager замість lazy.';

$_lang['setting_imageoptimizer_cleanup_on_uninstall'] = 'Очищення при видаленні';
$_lang['setting_imageoptimizer_cleanup_on_uninstall_desc'] = 'Видалити варіанти, таблицю черги та кеш при uninstall пакета.';

$_lang['setting_imageoptimizer_disk_warn_gb'] = 'Поріг попередження диска (ГБ)';
$_lang['setting_imageoptimizer_disk_warn_gb_desc'] = 'Показувати попередження в адмінці, якщо вільно менше N ГБ.';

$_lang['setting_imageoptimizer_stuck_minutes'] = 'Таймаут завислих (хвилини)';
$_lang['setting_imageoptimizer_stuck_minutes_desc'] = 'Скидати processing довше N хвилин.';

$_lang['setting_imageoptimizer_max_memory_limit'] = 'Ліміт пам\'яті';
$_lang['setting_imageoptimizer_max_memory_limit_desc'] = 'Підвищення memory_limit для великих файлів (наприклад 512M).';
