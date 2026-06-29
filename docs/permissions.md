# Права доступа

ImageOptimizer регистрирует три permission в namespace `imageoptimizer`. Назначение по умолчанию — группы **Administrator** и **Manager** (`_build/resolvers/resolver_policy.php`).

## Permissions

| Permission | Lexicon area | Описание |
|------------|--------------|----------|
| `imageoptimizer_view` | `area.imageoptimizer.view` | Просмотр: очередь, статистика, Server, Compatibility, чтение настроек |
| `imageoptimizer_settings` | `area.imageoptimizer.settings` | Сохранение системных настроек `imageoptimizer_*` |
| `imageoptimizer_run` | `area.imageoptimizer.run` | Операции с очередью: process, rebuild, retry, clear, reset stuck |

## Connector actions и ACL

**URL:** `assets/components/imageoptimizer/connector.php`  
**Метод:** POST, параметр `action`

| action | Требуемое permission |
|--------|----------------------|
| `queue/list` | `imageoptimizer_view` |
| `stats/summary` | `imageoptimizer_view` |
| `settings/get` | `imageoptimizer_view` |
| `server/check` | `imageoptimizer_view` |
| `compatibility/list` | `imageoptimizer_view` |
| `settings/update` | `imageoptimizer_settings` |
| `queue/retry` | `imageoptimizer_run` |
| `queue/rebuild` | `imageoptimizer_run` |
| `queue/clear` | `imageoptimizer_run` |
| `queue/reset_stuck` | `imageoptimizer_run` |
| `queue/process` | `imageoptimizer_run` |

Без авторизации в mgr → HTTP **401**.  
Без нужного permission → HTTP **403**.

## Поведение UI

- Пункт меню **ImageOptimizer** скрыт без `imageoptimizer_view`
- Вкладка **Настройки**: форма read-only или скрыта без `imageoptimizer_settings`
- Кнопки **Обработать очередь**, **Retry**, **Rebuild**, **Clear**, **Reset stuck** — только с `imageoptimizer_run`

## Настройка для кастомных ролей

### Редактор контента (только просмотр очереди)

Добавьте в группу:

- `imageoptimizer_view`

### Контент-менеджер (загрузка файлов + rebuild)

- `imageoptimizer_view`
- `imageoptimizer_run`

### Технический администратор (полный доступ)

- `imageoptimizer_view`
- `imageoptimizer_settings`
- `imageoptimizer_run`

Путь в MODX 3: **Настройки → Права доступа → Policies / User groups**.

## CLI и cron

CLI/cron инициализируют MODX как `mgr` без сессии пользователя — ACL connector на cron не распространяется. Ограничьте доступ к shell и crontab на уровне сервера.

## Связанные документы

- [manager-guide.md](manager-guide.md) — действия в интерфейсе
- [developer-guide.md](developer-guide.md) — полный список handlers
