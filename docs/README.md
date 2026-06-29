# Документация ImageOptimizer

Индекс материалов в корне репозитория (`docs/`).

**Версия:** 1.0.0-beta1 · **Требования:** MODX 3.0+, PHP 8.2+, pdoTools, VueTools >= 1.1.2-pl. MiniShop3 — опционально (инъекция на витрине без правки чанков).

## Быстрый старт

| Документ | Описание |
|----------|----------|
| [installation.md](installation.md) | Установка транспорта, npm-сборка админки, первый запуск, демо-стенд |
| [configuration.md](configuration.md) | Все системные настройки `imageoptimizer_*`, рекомендуемые пресеты |
| [frontend-guide.md](frontend-guide.md) | Авто-`<picture>`, пропуски, lazy, sizes, ручной вывод вариантов |
| [cli.md](cli.md) | CLI `convert.php`, cron, prune, аргументы и примеры |

## Админка и эксплуатация

| Документ | Описание |
|----------|----------|
| [manager-guide.md](manager-guide.md) | Обзор, очередь, **Обработать очередь**, rebuild по файлу/папке |
| [permissions.md](permissions.md) | Права `imageoptimizer_view`, `imageoptimizer_settings`, `imageoptimizer_run` |
| [server-requirements.md](server-requirements.md) | PHP, GD/Imagick, cwebp, avifenc, cron, VueTools |
| [compatibility.md](compatibility.md) | Thumb3x, pThumb, MiniShop3, VueTools |
| [troubleshooting.md](troubleshooting.md) | Типовые сбои: очередь, инъекция, память, админка |
| [faq.md](faq.md) | Частые вопросы и короткие ответы |

## Разработка и QA

| Документ | Описание |
|----------|----------|
| [developer-guide.md](developer-guide.md) | Архитектура, connector API, события, модель `ioQueue` |
| [testing.md](testing.md) | PHPUnit, демо-шаблон, seed-скрипт, smoke-чеклист |
| [../prd.md](../prd.md) | PRD и статус реализации |

## Примеры и демо

| Файл | Описание |
|------|----------|
| [../core/elements/templates/demo/imageoptimizer_test.tpl](../core/elements/templates/demo/imageoptimizer_test.tpl) | QA-страница: 20 секций (baseline, skip, products, MS3 resources, SVG…) |
| [../../core/elements/demo/seed_imageoptimizer_demo.php](../../core/elements/demo/seed_imageoptimizer_demo.php) | Сидер: demo-изображения, шаблон, ресурс, очередь (на dev-сайте MODX) |

URL демо после seed: `/imageoptimizer-test.html` (или alias ресурса, см. seed-скрипт).

## В пакете MODX (transport)

В `core/components/imageoptimizer/docs/`: **changelog.txt**, **readme.txt**, **license.txt**.

Корневой [README](../README.md) — краткая справка для репозитория.

---

## Навигация по ролям

### Менеджер / администратор сайта

1. [installation.md](installation.md) — установка и cron
2. [manager-guide.md](manager-guide.md) — работа с очередью
3. [configuration.md](configuration.md) — настройки
4. [faq.md](faq.md) — если что-то не работает

### Frontend-разработчик

1. [frontend-guide.md](frontend-guide.md) — инъекция и пропуски
2. [testing.md](testing.md) — демо-страница QA
3. [compatibility.md](compatibility.md) — Thumb3x и MS3

### Backend / maintainer

1. [developer-guide.md](developer-guide.md) — API и события
2. [cli.md](cli.md) — bulk и cron
3. [testing.md](testing.md) — PHPUnit

**Дата документации:** 28.06.2026
