# Руководство разработчика ImageOptimizer

## Архитектура

```
core/components/imageoptimizer/
├── bootstrap.php
├── controllers/index.class.php      — Vue-админка (VueTools)
├── include/
│   ├── paths.php                    — media sources, absolute paths
│   ├── settings.php                 — imageoptimizer_get_setting()
│   ├── queue.php                    — imageoptimizer_queue_*
│   ├── convert.php                  — энкодеры GD / Imagick / CLI
│   ├── picture_builder.php          — сборка <picture>, collect variants
│   ├── inject.php                   — OnWebPagePrerender
│   ├── html_parser.php              — DOMDocument, UTF-8, raw blocks, serialize
│   ├── img_skip_rules.php           — ImageOptimizerImgSkipRules
│   ├── file_lifecycle.php           — scan, rebuild_path, upload events
│   ├── preflight.php                — SkipReason до enqueue
│   ├── enum_status.php              — QueueStatus, SkipReason
│   ├── html_cache.php               — кэш inject, generation counter
│   ├── server_check.php
│   ├── handlers.php                 — маршрутизация connector
│   ├── handlers_queue.php
│   ├── handlers_mgr.php
│   └── events.php                   — upload + prerender
├── model/imageoptimizer/ioqueue.class.php
├── elements/plugins/plugin.imageoptimizer.php
├── cli/convert.php
├── cron/convert.php, cron/prune.php
└── lexicon/{ru,en,uk}/

assets/components/imageoptimizer/
├── connector.php
├── js/mgr/src/                      — Vue 3 tabs
│   └── composables/useQueueProcessor.js  — батчи queue/process до pending=0
└── js/mgr/vue-dist/                 — production bundle
```

Логика — функции `imageoptimizer_*` в `include/`, без monolithic service class.

## Сборка

```bash
pnpm install   # или npm install
pnpm run build:mgr
php _build/build.php
```

Конфиг: `_build/config.inc.php`, resolvers в `_build/resolvers/`.

## Connector API

**URL:** `{assets_url}components/imageoptimizer/connector.php`
**Метод:** POST
**Параметр:** `action`
**Контекст:** mgr (иначе 401)

| action | Permission | Назначение |
|--------|------------|------------|
| `queue/list` | view | Список очереди, фильтры |
| `queue/retry` | run | failed → pending |
| `queue/rebuild` | run | Scan + enqueue |
| `queue/clear` | run | Очистка очереди / HTML cache |
| `queue/reset_stuck` | run | Сброс зависших processing |
| `queue/process` | run | Обработка pending (worker cron/CLI) |
| `stats/summary` | view | Сводка для Overview |
| `settings/get` | view | Все настройки |
| `settings/update` | settings | Сохранение |
| `server/check` | view | Диагностика PHP/энкодеров |
| `compatibility/list` | view | Thumb3x / MS3 / VueTools |

Регистрация: `imageoptimizer_action_handlers()` в `handlers.php`.
Обработка очереди из UI: `imageoptimizer_handle_queue_process()` в `handlers_queue.php` (lock `cron`, `imageoptimizer_process_queue()`).

Vue-клиент (`useQueueProcessor.js`) вызывает `queue/process` в цикле до `pending === 0`, с retry при `409 worker_busy`.

Подробные параметры POST, JSON-ответы и PHP-функции — [api.md](api.md).

## Модель ioQueue

Таблица: `imageoptimizer_queue`

| Поле | Описание |
|------|----------|
| source | modMediaSource.id |
| path | Относительный путь в source |
| format | webp, avif, … |
| width | 0 = full-size variant |
| status | pending, processing, done, failed, skipped |
| skip_reason | SkipReason value при skipped |
| original_size, converted_size | bytes |
| error | Текст ошибки |
| created_at, processed_at, locked_at | datetime |

Unique index: `(source, path, format, width)`.

## Enums

`include/enum_status.php`:

- **QueueStatus** — pending, processing, done, failed, skipped
- **SkipReason** — UpscaleSkip, SvgSkip, HasSrcset, SrcPattern, DataSkip, MemoryLimit, …

## События плагина

| Событие | Функция | Действие |
|---------|---------|----------|
| `OnFileManagerUpload` | events.php | enqueue + optional sync convert |
| `OnWebPagePrerender` | events.php | `imageoptimizer_inject_html()` |

Регистрация: `_build/elements/plugins.php`.

## Конвертация

1. `imageoptimizer_enqueue_variants()` — breakpoints × formats
2. `imageoptimizer_queue_claim()` — pending → processing (с lock)
3. `imageoptimizer_convert_queue_item()` — resize, encode, write рядом с original
4. `imageoptimizer_queue_mark_done|failed|skipped()`

### Rebuild / scan

- `imageoptimizer_scan_source($modx, $sourceId, $subdir, $enqueue)` — рекурсивный обход каталога
- `imageoptimizer_rebuild_path($modx, $sourceId, $path, $dryRun)` — один файл **или** каталог (используется в `queue/rebuild` и CLI `--path` без `--scan`)

Имена файлов: `imageoptimizer_build_variant_path()` + `variant_pattern`.

Энкодеры по `method_priority`: cwebp, avifenc, gd, imagick.

## Инъекция HTML

`imageoptimizer_inject_html()`:

1. HTML cache hit → return cached (если `html_cache_allowed`)
2. `imageoptimizer_extract_raw_blocks()` — вырезать script/style/noscript/textarea
3. DOMDocument через `html_parser.php` + `ImageOptimizerImgSkipRules`
4. `imageoptimizer_resolve_img_asset()` + `imageoptimizer_collect_variants()`
5. `imageoptimizer_build_picture_element()` → replace img
6. `imageoptimizer_serialize_document()` + restore raw blocks
7. Save HTML cache if enabled

Ключ кэша: context, URI, editedon, settings hash, variants generation, **content hash**, `IMAGEOPTIMIZER_HTML_SERIALIZE_REV`.

SkipReason на inject отдельно от queue skip (см. `img_skip_rules.php`).

## Тесты

```bash
composer install
vendor/bin/phpunit
```

Основные классы:

- `tests/Unit/HtmlParserTest.php` — UTF-8, full document, raw blocks, entities
- `tests/Unit/InjectTest.php`
- `tests/Unit/ImgSkipRulesTest.php`
- `tests/Unit/PreflightTest.php`
- `tests/Unit/ResponsiveTest.php`
- `tests/Unit/HtmlCacheTest.php`
- `tests/Unit/MediaAccessTest.php`

См. [testing.md](testing.md).

## Расширение

- Новый skip: расширить `ImageOptimizerImgSkipRules` или настройки `skip_*`
- Новый формат: `convert.php`, settings `formats`, preflight
- Смена serialize output: bump `IMAGEOPTIMIZER_HTML_SERIALIZE_REV` в `html_parser.php`
- Отключить inject и вызывать `picture_builder` из сниппета

## Связанные документы

- [api.md](api.md)
- [../prd.md](../prd.md)
- [cli.md](cli.md)
- [permissions.md](permissions.md)
