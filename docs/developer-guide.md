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
│   ├── img_skip_rules.php           — ImageOptimizerImgSkipRules
│   ├── file_lifecycle.php           — scan, rebuild_path, upload events
│   ├── preflight.php                — SkipReason до enqueue
│   ├── enum_status.php              — QueueStatus, SkipReason
│   ├── html_cache.php               — кэш inject
│   ├── server_check.php
│   ├── handlers.php                 — маршрутизация connector
│   ├── handlers_queue.php
│   ├── handlers_mgr.php
│   └── events.php                   — upload + prerender
├── model/imageoptimizer/ioqueue.class.php
├── elements/plugins/imageoptimizer.php
├── cli/convert.php
├── cron/convert.php, cron/prune.php
└── lexicon/{ru,en}/

assets/components/imageoptimizer/
├── connector.php
├── js/mgr/src/                      — Vue 3 tabs
└── js/mgr/vue-dist/                 — production bundle
```

Логика — функции `imageoptimizer_*` в `include/`, без monolithic service class.

## Сборка

```bash
npm install
npm run build:mgr   # alias: npm run build
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

1. HTML cache hit → return cached
2. DOMDocument + `ImageOptimizerImgSkipRules`
3. `imageoptimizer_resolve_img_asset()` + `imageoptimizer_collect_variants()`
4. `imageoptimizer_build_picture_element()` → replace img
5. Save HTML cache if enabled

SkipReason на inject отдельно от queue skip (см. `img_skip_rules.php`).

## Тесты

```bash
composer install
vendor/bin/phpunit
```

Основные классы:

- `tests/Unit/InjectTest.php`
- `tests/Unit/ImgSkipRulesTest.php`
- `tests/Unit/PreflightTest.php`
- `tests/Unit/ResponsiveTest.php`
- `tests/Unit/HtmlCacheTest.php`

См. [testing.md](testing.md).

## Расширение

- Новый skip: расширить `ImageOptimizerImgSkipRules` или настройки `skip_*`
- Новый формат: `convert.php`, settings `formats`, preflight
- Отключить inject и вызывать `picture_builder` из сниппета

## Связанные документы

- [api.md](api.md)
- [../prd.md](../prd.md)
- [cli.md](cli.md)
- [permissions.md](permissions.md)
