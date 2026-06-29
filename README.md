# ImageOptimizer

Бесплатное дополнение для **MODX Revolution 3**: конвертация изображений в WebP/AVIF, responsive srcset по breakpoints, очередь с обработкой из админки (**Обработать очередь**), CLI/cron и автоматическая инъекция `<picture>` в HTML без правки чанков.

## Требования

- MODX 3.0+, PHP 8.2+
- [pdoTools](https://modx.pro/components/pdotools), [VueTools](https://docs.modx.pro/components/vuetools/) ≥ 1.1.2-pl
- GD или Imagick с WebP; AVIF опционально

## Сборка

```bash
npm install
npm run build:mgr
php _build/build.php
```

## Документация

Полный индекс: **[docs/README.md](docs/README.md)**

| Раздел | Файл |
|--------|------|
| Установка | [docs/installation.md](docs/installation.md) |
| Настройки | [docs/configuration.md](docs/configuration.md) |
| Админка | [docs/manager-guide.md](docs/manager-guide.md) |
| Фронтенд | [docs/frontend-guide.md](docs/frontend-guide.md) |
| CLI / cron | [docs/cli.md](docs/cli.md) |
| Разработка | [docs/developer-guide.md](docs/developer-guide.md) |

## Лицензия

См. `core/components/imageoptimizer/docs/license.txt`.
