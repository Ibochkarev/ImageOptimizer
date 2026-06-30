# ImageOptimizer — справочник API

Connector (Vue-админка), PHP-функции и форматы ответов. CLI/cron — [cli.md](cli.md). Права — [permissions.md](permissions.md). Настройки — [configuration.md](configuration.md). Архитектура — [developer-guide.md](developer-guide.md).

**Namespace настроек:** `imageoptimizer`

---

## Connector API

**URL:** `{assets_url}components/imageoptimizer/connector.php`  
**Метод:** POST  
**Параметр:** `action`  
**Контекст:** mgr (сессия + modAuth). Без авторизации — HTTP 401. Без permission — 403.

### Общий формат ответа

**Успех:**

```json
{
  "success": true,
  "object": { },
  "total": 0
}
```

**Ошибка:**

```json
{
  "success": false,
  "message": "…",
  "code": "error_key"
}
```

| HTTP | Причина |
|------|---------|
| 401 | Нет сессии mgr |
| 403 | Нет permission на action |
| 409 | `worker_busy` — lock cron уже занят |

### queue/list

| Permission | `imageoptimizer_view` |

**Параметры (POST):**

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `offset` | int | 0 | Смещение |
| `limit` | int | 50 | Лимит (1–500) |
| `status` | string | — | `pending`, `processing`, `done`, `failed`, `skipped` |
| `source` | int | — | ID media source |
| `query` | string | — | Поиск по path (LIKE) |

**Ответ:** массив записей `ioQueue` в `object`, `total` — общее число.

### queue/process

| Permission | `imageoptimizer_run` |

Запуск worker (тот же код, что cron/CLI). Блокировка: `core/cache/imageoptimizer/cron.lock`.

**Параметры:**

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `limit` | int | `cron_limit` | Сколько pending обработать (1–500) |

**Ответ (`object`):**

```json
{
  "processed": 42,
  "reset": 0,
  "queue": { "pending": 10, "done": 100, "failed": 2 },
  "limit": 200,
  "time_budget_exceeded": false
}
```

### queue/rebuild

| Permission | `imageoptimizer_run` |

Scan и постановка задач в очередь.

**Параметры:**

| Параметр | Тип | Описание |
|----------|-----|----------|
| `source` | int | ID media source (по умолчанию `default_media_source`) |
| `path` | string | Файл или каталог относительно basePath source. Пусто — весь source |
| `dry_run` | bool | Только подсчёт файлов, без enqueue |

**Ответ:**

```json
{ "enqueued": 14, "dry_run": false }
```

### queue/retry

| Permission | `imageoptimizer_run` |

**Параметры:** `ids` — массив ID или CSV.  
**Действие:** `failed` / `skipped` → `pending`.  
**Ответ:** `{ "updated": N }`.

### queue/clear

| Permission | `imageoptimizer_run` |

Удаление файлов вариантов и записей очереди.

**Параметры:** `source`, `path`, `dry_run` (аналогично rebuild).  
**Ответ:** `{ "removed": N, "dry_run": bool }`. Сбрасывает HTML-кэш инъекции.

### queue/reset_stuck

| Permission | `imageoptimizer_run` |

Сброс `processing` старше `stuck_minutes` → `pending`.  
**Ответ:** `{ "reset": N }`.

### stats/summary

| Permission | `imageoptimizer_view` |

Сводка для вкладки «Обзор»: счётчики очереди, прогресс, готовность сервера.

### settings/get | settings/update

| Permission | view / `imageoptimizer_settings` |

Чтение и сохранение всех `imageoptimizer_*`. Update принимает объект ключ → значение.

### server/check | compatibility/list

| Permission | `imageoptimizer_view` |

Диагностика PHP/энкодеров и статус Thumb3x / MiniShop3 / VueTools.

---

## PHP API (основные функции)

Подключение: `require_once MODX_CORE_PATH . 'components/imageoptimizer/include/helpers.php';`  
(или через bootstrap плагина / CLI).

```php
// Scan каталога media source
$count = imageoptimizer_scan_source($modx, $sourceId, 'assets/images', true);

// Rebuild: один файл или каталог
$count = imageoptimizer_rebuild_path($modx, $sourceId, 'images/resources', false);

// Обработка очереди
$processed = imageoptimizer_process_queue($modx, 100);

// Inject HTML (обычно вызывается плагином)
$html = imageoptimizer_inject_html($modx, $html, $resourceId);
```

**Модель:** `ioQueue`, таблица `imageoptimizer_queue`.  
**Статусы:** `QueueStatus` — pending, processing, done, failed, skipped.  
**Skip при конвертации:** `SkipReason` — UpscaleSkip, SvgSkip, MemoryLimit, …

---

## Связанные документы

- [developer-guide.md](developer-guide.md) — архитектура, события, конвертация
- [cli.md](cli.md) — `convert.php`, cron, аргументы
- [permissions.md](permissions.md) — ACL
- [configuration.md](configuration.md) — все `imageoptimizer_*`
