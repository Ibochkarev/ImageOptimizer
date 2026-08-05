# Документация ImageOptimizer

Индекс материалов в корне репозитория (`docs/`).

**Версия:** 1.0.4-beta1 · **Требования:** MODX 3.0+, PHP 8.2+, pdoTools, VueTools ≥ 1.1.2-pl. MiniShop3 — опционально (инъекция на витрине без правки чанков).

## Быстрый старт

| Документ | Описание |
|----------|----------|
| [installation.md](installation.md) | Установка транспорта, npm-сборка админки, первый запуск |
| [configuration.md](configuration.md) | Все системные настройки `imageoptimizer_*`, рекомендуемые пресеты |
| [frontend-guide.md](frontend-guide.md) | Авто-`<picture>`, пропуски, lazy, sizes, UTF-8, HTML-кэш |
| [cli.md](cli.md) | CLI `convert.php`, cron, prune, аргументы и примеры |

## Админка и эксплуатация

| Документ | Описание |
|----------|----------|
| [manager-guide.md](manager-guide.md) | Обзор, очередь, **Обработать очередь** (батчи до нуля, **Остановить**), rebuild |
| [permissions.md](permissions.md) | Права `imageoptimizer_view`, `imageoptimizer_settings`, `imageoptimizer_run` |
| [server-requirements.md](server-requirements.md) | PHP, GD/Imagick, cwebp, avifenc, cron, VueTools |
| [compatibility.md](compatibility.md) | Thumb3x, pThumb, MiniShop3, VueTools |
| [troubleshooting.md](troubleshooting.md) | Очередь, инъекция, память, UTF-8, админка |
| [faq.md](faq.md) | Частые вопросы |

## Разработка и QA

| Документ | Описание |
|----------|----------|
| [developer-guide.md](developer-guide.md) | Архитектура, connector API, события, модель `ioQueue` |
| [api.md](api.md) | Connector: параметры, JSON-ответы, PHP-функции |
| [testing.md](testing.md) | PHPUnit, smoke-чеклист, регрессия админки |
| [../prd.md](../prd.md) | PRD и статус реализации |

## В пакете MODX (transport)

В `core/components/imageoptimizer/docs/`: **changelog.txt**, **readme.txt**, **license.txt**.

Корневой [README](../README.md) — краткая справка для репозитория.

Публичная копия документации: [docs.modx.pro/components/imageoptimizer/](https://docs.modx.pro/components/imageoptimizer/)

---

## Навигация по ролям

### Менеджер / администратор сайта

1. [installation.md](installation.md) — установка и cron
2. [manager-guide.md](manager-guide.md) — очередь и **Обработать очередь**
3. [configuration.md](configuration.md) — настройки
4. [faq.md](faq.md) — если что-то не работает

### Frontend-разработчик

1. [frontend-guide.md](frontend-guide.md) — инъекция, пропуски, кириллица
2. [testing.md](testing.md) — smoke и ручная проверка фронта
3. [compatibility.md](compatibility.md) — Thumb3x и MS3

### Backend / maintainer

1. [developer-guide.md](developer-guide.md) — архитектура и события
2. [api.md](api.md) — connector и PHP API
3. [cli.md](cli.md) — bulk и cron
4. [testing.md](testing.md) — PHPUnit

**Дата документации:** 05.08.2026
