# Руководство по админке ImageOptimizer

URL: **Компоненты → ImageOptimizer** или `manager/?a=index&namespace=imageoptimizer`.

Интерфейс на Vue 3 + PrimeVue (через VueTools). Требуются права `imageoptimizer_view` (просмотр) и при необходимости `imageoptimizer_settings`, `imageoptimizer_run` — см. [permissions.md](permissions.md).

## Обзор

Сводка состояния компонента:

- статистика очереди: pending, processing, done, failed, skipped
- прогресс выполнения (% done)
- готовность сервера (энкодеры WebP/AVIF)
- кнопки **Обработать очередь**, **Остановить** и **Сбросить зависшие** (при `imageoptimizer_run`)

Переключатель **Live** обновляет цифры на экране каждые 5 секунд. Подсказка под переключателем: для фоновой обработки без открытой вкладки настройте cron (команда на вкладке **Server**).

## Очередь

Таблица `imageoptimizer_queue`: каждая строка — один вариант (исходник + ширина + формат).

### Колонки

| Колонка | Значение |
|---------|----------|
| ID | Первичный ключ |
| Source | ID media source |
| Path | Относительный путь в source |
| Format | `webp`, `avif`, … |
| Width | Целевая ширина (0 = full-size WebP/AVIF) |
| Status | `pending`, `processing`, `done`, `failed`, `skipped` |
| Sizes | `original_size → converted_size` (байты) |
| Error | Текст последней ошибки |

### Фильтры

- по статусу
- поиск по пути
- пагинация (lazy load)
- **Live** — автообновление таблицы (только отображение, не worker)

### Действия

| Действие | Право | Connector | Описание |
|----------|-------|-----------|----------|
| **Обработать очередь** | `imageoptimizer_run` | `queue/process` | Конвертация pending батчами до `pending = 0` |
| **Остановить** | `imageoptimizer_run` | — | Прерывает цикл батчей (текущий HTTP-запрос дорабатывает) |
| **Пересобрать очередь** | `imageoptimizer_run` | `queue/rebuild` | Scan и enqueue |
| **Очистить варианты** | `imageoptimizer_run` | `queue/clear` | Удаление файлов вариантов и строк очереди |
| **Повторить выбранные** | `imageoptimizer_run` | `queue/retry` | failed/skipped → pending |
| **Сбросить зависшие** | `imageoptimizer_run` | `queue/reset_stuck` | processing → pending |

#### Обработать очередь

Кнопка на вкладках **Обзор** и **Очередь** запускает тот же worker, что **cron** и `cli/convert.php`. Поведение с 1.0.4:

1. Сбрасывает зависшие `processing` (как cron)
2. Берёт lock (`core/cache/imageoptimizer/cron.lock`). Параллельно с cron не стартует
3. Отправляет батчи `queue/process` по **`imageoptimizer_cron_limit`** задач за HTTP-запрос
4. Повторяет батчи, пока `pending` не станет 0 или вы не нажмёте **Остановить**
5. При `409 worker_busy` (cron уже работает) — до 3 повторов с паузой 2 с
6. Метка кнопки во время работы: «Обработка… осталось N»

Итоговые уведомления:

- очередь пуста — «Обработано N задач. Очередь пуста.»
- **Остановить** — «Остановлено. Обработано N, в очереди M.»
- лимит времени PHP — «Достигнут лимит времени PHP…» (остаток дожмёт cron или повторный клик)

Для больших каталогов без открытой вкладки используйте cron на вкладке **Server**.

#### Диалог «Пересобрать очередь»

1. **Media source** — ID источника (обычно `1`, Filesystem)
2. **Path** — **файл или папка** относительно **корня source**, не URL сайта:
   - пусто — весь source рекурсивно
   - `assets/images/products` — каталог MiniShop
   - `images/resources` — загрузки MS3 (если basePath = корень сайта)
   - `assets/test/hero.jpg` — один файл
3. **Только просмотр** — превью: сколько **файлов** попадёт в scan (не число строк очереди)
4. **Запустить** — постановка задач

После rebuild нажмите **Обработать очередь** или дождитесь cron.

### Типичные статусы

- **skipped** — upscale skip, SVG, уже WebP, animated GIF, HEIC без декодера, MemoryLimit
- **failed** — нет энкодера, ошибка диска. Смотрите Error и [troubleshooting.md](troubleshooting.md)

## Настройки

Форма всех `imageoptimizer_*` с группировкой по разделам (общие, форматы, фронтенд, обработка). Сохранение через connector → обновление `modSystemSetting`.

После смены breakpoints или formats для уже загруженных файлов нужен **rebuild** или повторная загрузка.

## Сервер

Проверка окружения (read-only):

- версия PHP, лимиты memory / execution time
- GD / Imagick: WebP, AVIF, HEIC
- CLI: `cwebp`, `avifenc`, `heif-convert` (с fallback на `/opt/homebrew/bin` при пустом PATH в FPM)
- готовность сервера (%)
- **Cron-команда** — копирование в буфер

Карточки энкодеров: **Доступен** / **Не найден**.

На **Valet/Herd** CLI и FPM могут быть разными PHP: imagick в CLI ≠ imagick в FPM. См. [server-requirements.md](server-requirements.md).

## Совместимость

Информация о связке с:

- **Thumb3x** / **pThumb** — skip по `skip_src_pattern`
- **MiniShop3** — инъекция на витрине без правки чанков
- **VueTools** — версия и статус

Подробнее: [compatibility.md](compatibility.md).

## Connector и ошибки UI

API: `assets/components/imageoptimizer/connector.php`, POST, параметр `action`, cookie сессии mgr + `HTTP_MODAUTH` / заголовок `Modauth`.

| HTTP | Причина |
|------|---------|
| **401** | Нет сессии mgr или неверный modAuth |
| **403** | Нет permission на action |
| **409** | `worker_busy` — cron или другой «Обработать» уже работает |

### «Unexpected token '<'» / invalid JSON

Ответ коннектора пришёл HTML вместо JSON (PHP warning, 404, или гонка загрузки Vue до `imageoptimizerConfig`). Обновите страницу (`Cmd+Shift+R`), проверьте `connector.php` в Network. Подробнее: [troubleshooting.md](troubleshooting.md).

Пустая страница / «VueTools not found» — установите VueTools, пересоберите `npm run build:mgr`.

## Связанные документы

- [configuration.md](configuration.md) — описание каждой настройки
- [cli.md](cli.md) — то же, что cron, из shell
- [testing.md](testing.md) — проверка после изменений
