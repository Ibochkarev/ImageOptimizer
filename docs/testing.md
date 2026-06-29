# Тестирование ImageOptimizer

Чеклисты для ручной проверки, smoke после сборки и регрессии фронта/админки.

## Быстрый smoke (5 минут)

1. **Админка** — `manager/?a=index&namespace=imageoptimizer` открывается без ошибок VueTools
2. **Server** — хотя бы WebP «Доступен»
3. **Overview** — JSON загружается (нет `Unexpected token '<'` в консоли)
4. Загрузить JPEG в File Manager → в **Очередь** появляются pending
5. **Обработать очередь** или `cli/convert.php --limit=10` → статус **done**, файлы `*.webp` на диске
6. Открыть страницу с `<img src="assets/...">` → в HTML есть `<picture>` с WebP

## Демо-страница QA

### Подготовка

```bash
php core/elements/demo/seed_imageoptimizer_demo.php
php core/elements/demo/seed_imageoptimizer_demo.php --run-cron   # опционально: convert сразу
```

Создаёт тестовые файлы, шаблон `imageoptimizer_test`, ресурс с alias **`imageoptimizer-test`**.

URL: `https://ваш-сайт/imageoptimizer-test.html` (зависит от friendly URLs).

### Секции шаблона (20 блоков)

| # | Секция | Что проверяет |
|---|--------|---------------|
| 0 | Навигация | Якоря по секциям |
| 1 | Baseline hero | `<picture>`, WebP, `srcset`, `sizes` |
| 2 | Карточки PNG | Несколько `<img>`, alpha |
| 3 | Skip по классу | `data-imageoptimizer-skip` / skip_classes |
| 4 | Skip по src | `skip_src_pattern` |
| 5 | Lazy loading | `loading="lazy"` сохраняется |
| 6 | fetchpriority | `fetchpriority="high"` на LCP |
| 7 | decoding async | `decoding="async"` |
| 8 | Свой атрибут sizes | `sizes` сохраняется |
| 9 | unknown-path | Локальный файл без вариантов — без `<picture>` |
| 9b | Внешний URL | Не трогаем http(s) src |
| 10 | MiniShop products | Галерея товаров MS3 |
| 11 | Demo home tiles | Сетка с локальными img |
| 12 | MS3 resources | `images/resources/…` |
| 13 | PNG alpha | Прозрачность в вариантах |
| 14 | Existing loading | Атрибуты не затираются |
| 15 | fetchpriority inline | В prose-блоке |
| 16 | Prose inline img | Inline в тексте |
| 17 | Existing decoding | decoding на месте |
| 18 | SVG skip | SvgSkip |
| 19 | Чеклист ручной проверки | Список для тестировщика |
| 20 | DOM report | Скрипт: число `<picture>`, `data-io-case` |

Каждый кейс помечен `data-io-case="…"` для поиска в DevTools.

### Проверка фронта

```bash
curl -s 'https://ваш-сайт/imageoptimizer-test.html' | grep -c '<picture'
curl -s 'https://ваш-сайт/imageoptimizer-test.html' | grep 'data-io-case'
```

Ожидание: после обработки очереди — `<picture>` на локальных img (не на skip/external/svg).

## PHPUnit

Из корня репозитория ImageOptimizer:

```bash
composer install
./vendor/bin/phpunit
```

Покрытие: preflight, skip rules, variant paths, queue helpers, rebuild_path (файл/каталог), connector handlers (моки).

## Сборка админки

```bash
npm install
npm run build:mgr
```

Проверить наличие `assets/components/imageoptimizer/js/mgr/vue-dist/imageoptimizer-admin.min.js`.

После правок JS — пересборка обязательна перед transport или rsync на dev-сайт.

## Регрессия админки

| Сценарий | Ожидание |
|----------|----------|
| Overview без прав | 403 / скрытое меню |
| queue/list | JSON, пагинация |
| queue/rebuild path=каталог | dry-run > 0 файлов |
| queue/process | processed ≥ 0, lock освобождён |
| queue/process параллельно cron | 409 worker_busy |
| settings/update без settings | 403 |
| Server | JSON с encoders |

## Регрессия очереди

1. Rebuild с путём **`images/resources`** (если MS3 кладёт туда) — dry-run показывает файлы
2. Rebuild с одним файлом `assets/test/imageoptimizer/hero.jpg` — 1+ задач
3. Clear — варианты удалены с диска
4. Retry failed → pending → process → done

## Связанные документы

- [manager-guide.md](manager-guide.md) — кнопки и диалоги
- [cli.md](cli.md) — `--scan`, `--path`
- [troubleshooting.md](troubleshooting.md) — если smoke падает
