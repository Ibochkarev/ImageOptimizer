# Troubleshooting

Пошаговая диагностика типовых проблем. Краткие ответы — в [faq.md](faq.md).

## Очередь не обрабатывается

### Симптомы

Записи остаются в `pending`, файлы `*.webp` не появляются.

### Шаги

1. **Админка** — **Очередь → Обработать очередь** (нужно `imageoptimizer_run`). Обрабатывает до `cron_limit` за клик. Для большой очереди — несколько раз или cron.
2. **Cron** — строка в crontab указывает на правильный PHP и путь:
   ```bash
   php core/components/imageoptimizer/cron/convert.php
   ```
3. **Lock** — если предыдущий процесс упал, удалите устаревший lock только после проверки, что worker не запущен:
   `core/cache/imageoptimizer/cron.lock`
4. **Ручной прогон:**
   ```bash
   php core/components/imageoptimizer/cli/convert.php --limit=10
   ```
5. **Зависшие processing** — **Очередь → Сбросить зависшие** или дождитесь cron (`stuck_minutes`).
6. **Энкодеры** — вкладка **Server**: WebP должен быть «Доступен».
7. **enabled** — `imageoptimizer_enabled=1`.
8. **409 worker_busy** — другой cron или «Обработать» уже работает. Подождите или снимите lock.

### Ошибка «Unable to create cache directory»

Worker не может создать `core/cache/imageoptimizer/`. Обновите пакет или вручную:

```bash
mkdir -p core/cache/imageoptimizer
chmod 775 core/cache/imageoptimizer
```

## Нет WebP/AVIF на сайте (нет `<picture>`)

### Чеклист

1. `imageoptimizer_enabled=1` и `imageoptimizer_inject_frontend=1`
2. Плагин **ImageOptimizer** активен, событие `OnWebPagePrerender` в списке
3. В очереди для этого пути статус **done** (не только pending)
4. `src` у `<img>` — локальный путь в media source (`assets/...`), не внешний URL
5. Нет skip: класс из `skip_classes`, `skip_src_pattern`, `data-imageoptimizer-skip`
6. **Кэш:** очистите кэш MODX. При `html_cache=1` — сброс HTML-кэша ImageOptimizer (см. [frontend-guide.md](frontend-guide.md))
7. Страница не больше `max_html_size` (1 МБ по умолчанию)

### Проверка HTML

```bash
curl -s 'https://example.com/page.html' | grep -E '<picture|\.webp'
```

### resolve_img_asset возвращает null

Частая причина на MODX 3 — media source не находился по legacy alias. Убедитесь, что установлена актуальная версия extra с fallback на `sources.modMediaSource`.

## Статус skipped в очереди

Смотрите колонку **skip_reason** / **Error**:

| Причина | Действие |
|---------|----------|
| Upscale skip | `upscale=0` и картинка меньше breakpoint — норма. Включите `upscale=1` если нужны «широкие» варианты |
| SvgSkip | SVG не конвертируется |
| AlreadyWebp | Источник уже WebP |
| AnimatedNotSupported | GIF с анимацией |
| MemoryLimit | Увеличьте `max_memory_limit`, уменьшите breakpoints |
| NonFilesystemSource | Очередь только для Filesystem media source |

## Ошибки памяти / timeout

- `imageoptimizer_max_memory_limit` → `512M` или `768M`
- Меньше breakpoints: `480,768,1024`
- Меньше `cron_limit` и `--limit` в CLI
- Конвертируйте большие каталоги ночью с `--time-budget=3600`

## Админка не открывается / белый экран

1. Установите **VueTools ≥ 1.1.2-pl**
2. Пересоберите бандл: `npm run build:mgr`
3. Очистите кэш браузера и MODX
4. Консоль браузера: ошибки загрузки `imageoptimizer-admin.min.js`
5. Сообщение `imageoptimizer_vuetools_required` — VueTools не установлен или версия ниже минимума

## JSON / «Unexpected token '<'» в Overview

Коннектор вернул HTML (PHP warning, редирект на login, 404) вместо JSON.

1. Hard refresh (`Cmd+Shift+R`)
2. DevTools → Network → `connector.php` — статус и тело ответа
3. Убедитесь, что `window.imageoptimizerConfig.connectorUrl` задан (актуальный бандл после `npm run build:mgr`)
4. Проверьте права и modAuth (401 → HTML страница mgr)

## Rebuild показывает 0 файлов для каталога

Путь в диалоге **относительно корня media source**, не URL сайта:

- MS3 uploads: часто `images/resources`, не `/images/resources`
- MiniShop: `assets/images/products/…`
- Один файл: полный относительный путь с расширением

Dry-run в диалоге считает **файлы**, не строки очереди (файл × breakpoints × formats).

## Энкодеры «Не найден» на Valet / Herd (macOS)

CLI `php -m` показывает imagick, а вкладка **Server** — нет: FPM использует другой бинарник и пустой `PATH`.

1. Один PHP для CLI и FPM: `valet use php@8.5` / Herd → та же версия
2. Установить imagick для **FPM PHP**: `pecl install imagick`
3. CLI-утилиты: `brew install webp libavif libheif imagemagick`
4. В `php-fpm.d/valet-fpm.conf` (или аналог): `env[PATH] = /opt/homebrew/bin:/usr/local/bin:…`
5. Перезапустить FPM (при «залипшем» master может понадобиться `sudo kill` старого php-fpm)

Preflight ищет `cwebp`, `avifenc`, `heif-convert` также в `/opt/homebrew/bin`. Подробнее: [server-requirements.md](server-requirements.md).

## Огромная очередь после rebuild

Rebuild по корню `assets/` ставит задачи на каждый файл × breakpoints × formats.

**Решение:** rebuild с узким `path` (`assets/images/catalog`), cron с меньшим `cron_limit`, или остановка и очистка лишних pending через `queue/clear` (осторожно — смотрите описание action в админке).

## Диск переполнен

Каждый вариант — отдельный файл. Сократите `formats`, `breakpoints` или `avif_enabled`. Настройте `disk_warn_gb` для раннего предупреждения в UI.

## Связанные документы

- [faq.md](faq.md)
- [server-requirements.md](server-requirements.md)
- [testing.md](testing.md)
