# Материалы для публикации ImageOptimizer

Тексты для [modstore.pro](https://modstore.pro), MODX.pro и репозитория. **Документация на MODX.pro:** [docs.modx.pro/components/imageoptimizer/](https://docs.modx.pro/components/imageoptimizer/). Локально: [README.md](../README.md), [docs/](../docs/README.md), [readme.txt](../core/components/imageoptimizer/docs/readme.txt), [changelog.txt](../core/components/imageoptimizer/docs/changelog.txt).

> **ImageOptimizer** — бесплатное дополнение для **MODX Revolution 3.x**. MiniShop3 не обязателен: инъекция `<picture>` работает на любом HTML-сайте.

## Файлы

| Файл | Назначение |
|------|------------|
| **DESCRIPTION.md** | Полное описание: возможности, админка, очередь, фронт, CLI, QA-стенд. |
| **modstore-description.md** | HTML для поля описания на modstore.pro (`<strong>`, `<ul>`, `<code>`). |
| **modx-pro-announcement.md** | Текст анонса для MODX.pro (HTML + `<cut>`). |
| **logo-320.png** | 320×320 — логотип карточки (добавьте при публикации). |
| **banner-1200x630.png** | 1200×630 — баннер для соцсетей / `og:image` (добавьте при публикации). |
| **screenshot-01-overview.png** | Вкладка «Обзор»: статистика очереди и готовность сервера. |
| **screenshot-02-queue.png** | Вкладка «Очередь»: таблица задач, фильтры, «Обработать очередь». |
| **screenshot-03-server.png** | Вкладка «Сервер»: энкодеры WebP/AVIF, совместимость. |
| **screenshot-04-picture.png** | `<picture>` с WebP в HTML страницы (View Source или DevTools). |

## Использование

- **ModStore:** **modstore-description.md** — в HTML-поле карточки. **DESCRIPTION.md** — развёрнутая основа, сократите под лимиты.
- **GitHub / About:** **DESCRIPTION.md** или корневой README.
- **MODX.pro:** **modx-pro-announcement.md** — в редактор статьи.
- **Соцсети:** **banner-1200x630.png**, когда файл будет готов.

Папка `_preview/` **не входит** в transport ZIP — только репозиторий и загрузка на ModStore.
