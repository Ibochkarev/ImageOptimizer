# Тестирование ImageOptimizer

Чеклисты для ручной проверки, smoke после сборки и регрессии фронта/админки.

## Быстрый smoke (5 минут)

1. **Админка** — `manager/?a=index&namespace=imageoptimizer` открывается без ошибок VueTools
2. **Server** — хотя бы WebP «Доступен»
3. **Overview** — JSON загружается (нет `Unexpected token '<'` в консоли)
4. Загрузить JPEG в File Manager → в **Очередь** появляются pending
5. **Обработать очередь** — батчи до `pending = 0` или `cli/convert.php --limit=10` → статус **done**, файлы `*.webp` на диске
6. Открыть страницу с `<img src="assets/...">` в режиме инкогнито → в HTML есть `<picture>` с WebP

## Ручная проверка фронта

Создайте тестовый ресурс с типовыми кейсами:

| Кейс | Разметка | Ожидание |
|------|----------|----------|
| Baseline | `<img src="assets/…/photo.jpg">` | `<picture>`, WebP srcset |
| Skip по классу | `class="no-optim"` | без `<picture>` |
| Skip по атрибуту | `data-imageoptimizer-skip` | без `<picture>` |
| Skip Thumb3x | `src` с `thumb3x` | без изменений |
| Lazy | обычный img | `loading="lazy"`, `decoding="async"` |
| LCP | `data-imageoptimizer-fetchpriority="high"` | fetchpriority сохранён |
| Свой sizes | атрибут `sizes` на img | не перезаписан |
| Existing srcset | img с `srcset` | srcset не трогаем |
| SVG | `logo.svg` | skip |
| Внешний URL | `https://…` | skip |
| Кириллица | текст «Тестовое cooperation» рядом с img | UTF-8 без `&#1044;` |
| Script safety | `<script>` с HTML-строками в JS | скрипт не ломается |

Проверка через curl (без авторизации, как гость):

```bash
curl -s 'https://ваш-сайт/test-page.html' | grep -c '<picture'
curl -s 'https://ваш-сайт/test-page.html' | grep -E 'webp|picture'
```

## PHPUnit

Из корня репозитория ImageOptimizer:

```bash
composer install
composer test
# или
./vendor/bin/phpunit
```

| Тест | Что покрывает |
|------|---------------|
| `HtmlParserTest` | UTF-8, full document, raw blocks, xml artifacts |
| `InjectTest` | inject pipeline |
| `ImgSkipRulesTest` | skip rules |
| `PreflightTest` | enqueue preflight |
| `ResponsiveTest` | breakpoints, variant paths |
| `HtmlCacheTest` | generation, cache keys |
| `MediaAccessTest` | media source resolve |

## Сборка админки

```bash
pnpm install   # или npm install
pnpm run build:mgr
```

Проверить наличие `assets/components/imageoptimizer/js/mgr/vue-dist/imageoptimizer-admin.min.js`.

После правок JS — пересборка обязательна перед transport или rsync на dev-сайт.

## Регрессия админки

| Сценарий | Ожидание |
|----------|----------|
| Overview без прав | 403 / скрытое меню |
| queue/list | JSON, пагинация |
| queue/rebuild path=каталог | dry-run > 0 файлов |
| **Обработать очередь** | батчи до pending=0, кнопка **Остановить** |
| Остановить во время process | cancelled, pending > 0 |
| queue/process параллельно cron | 409 worker_busy, UI retry |
| settings/update без settings | 403 |
| Server | JSON с encoders |
| Live toggle | цифры обновляются, worker не стартует |

## Регрессия очереди

1. Rebuild с путём **`images/resources`** (если MS3 кладёт туда) — dry-run показывает файлы
2. Rebuild с одним файлом `assets/test/hero.jpg` — 1+ задач
3. Clear — варианты удалены с диска
4. Retry failed → pending → process → done

## Связанные документы

- [manager-guide.md](manager-guide.md) — кнопки и диалоги
- [cli.md](cli.md) — `--scan`, `--path`
- [troubleshooting.md](troubleshooting.md) — если smoke падает
