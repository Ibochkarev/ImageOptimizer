# Требования сервера

Минимальная и рекомендуемая конфигурация для конвертации, очереди и Vue-админки.

## Платформа

| Компонент | Требование |
|-----------|------------|
| MODX Revolution | ≥ 3.0.0 |
| PHP | ≥ 8.2.0 (enum, typed properties) |
| MySQL / MariaDB | InnoDB для таблицы `imageoptimizer_queue` |
| pdoTools | Зависимость transport |
| VueTools | ≥ 1.1.2-pl для админки |

## PHP extensions

| Extension | Обязательно | Назначение |
|-----------|-------------|------------|
| `fileinfo` | да | MIME и preflight |
| `gd` **или** `imagick` | да (хотя бы одно) | Декодирование и resize |
| `json` | да | Connector / CLI JSON |

### WebP

Достаточно одного из:

- GD с поддержкой WebP (`imagewebp`)
- Imagick с WebP
- CLI **cwebp** (libwebp) — часто быстрее. Приоритет задаётся `method_priority`

### AVIF

Дополнительно:

- `imageoptimizer_avif_enabled=1` и `formats` с `avif`
- CLI **avifenc** (libavif) или Imagick с AVIF

Без AVIF-энкодера задачи AVIF получат `failed` или будут пропущены на preflight.

## Рекомендуемые лимиты PHP

| Параметр | Рекомендация | Комментарий |
|----------|--------------|-------------|
| `memory_limit` | ≥ 256M, для больших фото 512M | Дублируется в `max_memory_limit` на время convert |
| `max_execution_time` | ≥ 60 для CLI/cron | Cron укладывается в `time-budget` |
| `upload_max_filesize` | по политике сайта | File Manager upload |

## Диск и каталоги

ImageOptimizer должен писать в:

```
assets/...                    — варианты рядом с оригиналами (media source)
core/cache/imageoptimizer/    — temp, HTML cache, cron.lock
```

Права: пользователь PHP (www-data, nginx) — запись в media source и `core/cache/`.

Свободное место: планируйте ~ (число фото) × (breakpoints + 1) × (число formats) × средний размер варианта. Порог предупреждения: `disk_warn_gb`.

## Cron

- PHP CLI той же версии, что и FPM (проверьте `php -v` vs `php-fpm -v`)
- **flock** через lock-файл в `core/cache/imageoptimizer/cron.lock`
- Интервал 5–15 минут для умеренной нагрузки

Пример:

```bash
*/10 * * * * /usr/bin/php /var/www/html/core/components/imageoptimizer/cron/convert.php
0 3 * * * /usr/bin/php /var/www/html/core/components/imageoptimizer/cron/prune.php
```

## Проверка окружения

### В админке

**ImageOptimizer → Server** — PHP, memory, карточки энкодеров WebP/AVIF.

### Connector

POST `action=server/check` (требует `imageoptimizer_view`).

### CLI smoke

```bash
php core/components/imageoptimizer/cli/convert.php --limit=1 --json
```

## macOS: Valet / Herd

Частая проблема: **CLI PHP** (терминал) видит imagick и `/opt/homebrew/bin/cwebp`, а **php-fpm** (сайт и вкладка Server) — нет.

| Симптом | Действие |
|---------|----------|
| Server: «Не найден» при рабочем `php -m \| grep imagick` | Установите imagick для **той же** версии PHP, что FPM (`pecl install imagick`) |
| cwebp/avifenc не находятся | `brew install webp libavif libheif imagemagick`. Добавьте `env[PATH]` в pool FPM |
| Старый php-fpm после `brew services restart` | Убейте залипший master-процесс, перезапустите Valet/Herd |

Preflight дополнительно проверяет `/opt/homebrew/bin` и `/usr/local/bin`, если `PATH` в FPM пустой.

Рекомендация: `valet use php@X.Y` и один `php -v` для CLI и сокета FPM.

## Shared hosting

- Убедитесь, что cron доступен и `proc_open` не запрещён (для cwebp/avifenc)
- При запрете shell-утилит оставьте только GD/Imagick в `method_priority`
- Уменьшите `cron_limit` до 20–50

## Связанные документы

- [installation.md](installation.md)
- [cli.md](cli.md)
- [troubleshooting.md](troubleshooting.md)
