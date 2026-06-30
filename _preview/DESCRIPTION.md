# ImageOptimizer — WebP/AVIF и responsive `<picture>` для MODX 3

**ImageOptimizer** конвертирует изображения в **WebP** и опционально **AVIF**, создаёт responsive-варианты по breakpoints, ставит задачи в очередь и может **автоматически** оборачивать `<img>` в `<picture>` на выходе страницы — без правки чанков.

Системные настройки: namespace **`imageoptimizer`**, префикс **`imageoptimizer_`**. Админка на **Vue 3 + PrimeVue** (через **VueTools**).

## Документация

| Ресурс | Ссылка |
|--------|--------|
| **MODX.pro (основная)** | [docs.modx.pro/components/imageoptimizer/](https://docs.modx.pro/components/imageoptimizer/) |
| README репозитория | [README.md](../README.md) |
| Индекс docs (репозиторий) | [docs/README.md](../docs/README.md) |
| Установка | [docs/installation.md](../docs/installation.md) |
| Настройки | [docs/configuration.md](../docs/configuration.md) |
| Админка | [docs/manager-guide.md](../docs/manager-guide.md) |
| Фронтенд | [docs/frontend-guide.md](../docs/frontend-guide.md) |
| CLI / cron | [docs/cli.md](../docs/cli.md) |
| Changelog | [changelog.txt](../core/components/imageoptimizer/docs/changelog.txt) |

---

## Требования

| Компонент | Версия |
|-----------|--------|
| MODX Revolution | 3.0+ |
| PHP | 8.2+ |
| [pdoTools](https://modx.pro/components/pdotools) | актуальная |
| [VueTools](https://docs.modx.pro/components/vuetools/) | ≥ 1.1.2-pl |
| GD **или** Imagick | с WebP |
| AVIF | опционально (`avifenc` или Imagick) |
| MiniShop3 | опционально |

---

## Ключевые возможности

### Конвертация и очередь

- **WebP / AVIF** — статические файлы рядом с оригиналом (`hero.768.webp`, `card.png.webp`).
- **Breakpoints** — настраиваемые ширины (по умолчанию `480,768,1024,1440,1920`).
- **Upload** — постановка в очередь при загрузке в File Manager (`convert_on_upload`).
- **Очередь** — таблица `imageoptimizer_queue`, статусы pending / processing / done / failed / skipped.
- **Обработать очередь** — кнопка в админке (тот же worker, что cron и CLI).
- **Пересобрать** — scan **файла или каталога** относительно media source (MiniShop `assets/images/products`, MS3 `images/resources`).
- **CLI / cron** — `cli/convert.php`, `cron/convert.php`, `cron/prune.php`.

### Фронтенд

- **OnWebPagePrerender** — парсинг HTML, обёртка в `<picture>` с WebP/AVIF `srcset`.
- **Пропуски** — `skip_classes`, `skip_src_pattern` (Thumb3x), `data-imageoptimizer-skip`, внешние URL, SVG, файлы без вариантов.
- **Сохранение атрибутов** — `loading`, `decoding`, `sizes`, существующий `<picture>` / `srcset`.
- **HTML-кэш** инъекции — опционально (`html_cache`).

### Админка (Vue)

- **Обзор** — статистика очереди, готовность сервера, Live-обновление.
- **Очередь** — фильтры, retry, clear, reset stuck, **process**, rebuild.
- **Настройки** — все `imageoptimizer_*`.
- **Сервер** — PHP, энкодеры (GD, Imagick, cwebp, avifenc), cron-команда.
- **Совместимость** — Thumb3x, pThumb, MiniShop3, VueTools.

### Права

- `imageoptimizer_view` — просмотр
- `imageoptimizer_settings` — настройки
- `imageoptimizer_run` — process, rebuild, retry, clear

---

## Как это работает

1. Загрузка JPEG/PNG в File Manager → `OnFileManagerUpload` → задачи в очередь (breakpoints × formats).
2. Worker (кнопка **Обработать**, cron или CLI) → resize, encode, запись `*.webp` / `*.avif` рядом с original.
3. `OnWebPagePrerender` → для локальных `<img>` с готовыми вариантами → `<picture>` + `srcset` + `sizes`.
4. Браузер получает меньший WebP/AVIF там, где поддерживается.

Thumb3x / pThumb URL с `thumb3x` не трогаются — on-the-fly и статические варианты не дублируются.

---

## Системные настройки (основные)

| Ключ | Назначение |
|------|------------|
| `imageoptimizer_enabled` | Глобальный выключатель |
| `imageoptimizer_inject_frontend` | Авто-`<picture>` на выходе страницы |
| `imageoptimizer_convert_on_upload` | Очередь при upload |
| `imageoptimizer_formats` | `webp`, `avif` |
| `imageoptimizer_breakpoints` | Ширины вариантов |
| `imageoptimizer_quality` / `quality_jpeg` / `quality_png` | Качество |
| `imageoptimizer_method_priority` | `cwebp,gd,imagick` |
| `imageoptimizer_cron_limit` | Задач за запуск worker |
| `imageoptimizer_skip_classes` | CSS-классы без инъекции |
| `imageoptimizer_skip_src_pattern` | Подстрока URL для skip |
| `imageoptimizer_skip_lazy_first_images` | Первые N `<img>` → `loading=eager` |
| `imageoptimizer_html_cache` | Кэш HTML после inject |

Полный список: [docs/configuration.md](../docs/configuration.md).

---

## Установка и первый запуск

1. Установите **pdoTools** и **VueTools** (≥ 1.1.2-pl).
2. Загрузите transport через «Управление пакетами».
3. Откройте **Компоненты → ImageOptimizer** → вкладка **Сервер** (WebP «Доступен»).
4. Настройте cron **или** обрабатывайте очередь кнопкой **Обработать очередь**:

```bash
*/10 * * * * php /path/to/core/components/imageoptimizer/cron/convert.php
```

5. Для существующих каталогов — **Пересобрать очередь** с path (`assets/images/catalog`, `images/resources`) или CLI:

```bash
php core/components/imageoptimizer/cli/convert.php --source=1 --scan --path=assets/images --limit=500
```

6. Очистите кэш MODX после массовой конвертации.

---

## QA-стенд

На dev-сайте (шаблоны в `core/elements/`):

```bash
php core/elements/demo/seed_imageoptimizer_demo.php
```

Создаёт демо-файлы, ресурс **`imageoptimizer-test`**, шаблон с **20 секциями** QA:

- baseline, skip (URL / классы / атрибут), existing picture/srcset
- lazy-order с `globalIndex` в DOM-отчёте
- unknown-path (`imageoptimizer-qa/unqueued.jpg`) и external URL
- MiniShop products, demo tiles, MS3 `images/resources`
- PNG alpha, loading/decoding/fetchpriority, prose, SVG

URL: `/imageoptimizer-test.html`. Подробнее: [docs/testing.md](../docs/testing.md).

---

## Сборка транспорта

```bash
npm install
npm run build:mgr
php _build/build.php
```

Архив: `core/packages/imageoptimizer-*.transport.zip`. `build.php` при наличии `package.json` собирает Vue-бандл админки.

---

## Почему ImageOptimizer

- **Статические** WebP/AVIF — CDN и браузер отдают файлы без PHP на каждый запрос.
- **Без правки чанков** — inject на prerender.
- **Очередь из админки** — не только cron.
- **Bulk rebuild** — каталог MiniShop или MS3 одним path.
- **Бесплатно** — см. [license.txt](../core/components/imageoptimizer/docs/license.txt).
