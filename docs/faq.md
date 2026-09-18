# FAQ — ImageOptimizer

## Общее

### Чем ImageOptimizer отличается от Thumb3x / pThumb?

ImageOptimizer создаёт **статические** WebP/AVIF рядом с оригиналом и может **автоматически** оборачивать `<img>` в `<picture>` на выходе страницы. Thumb3x/pThumb — on-the-fly ресайз по URL. Их можно использовать вместе, но не дублируйте responsive на одних и тех же картинках ([compatibility.md](compatibility.md)).

### Нужен ли MiniShop3?

Нет. Инъекция работает на любом HTML-сайте MODX. MS3 упоминается как типичный кейс витрины.

### Ломается ли SVG?

SVG не конвертируется и по умолчанию не попадает в очередь (растровые форматы: jpg, png, gif, webp как source).

## Очередь и конвертация

### После установки тысячи pending — это нормально?

Если вы сделали rebuild по корню `assets/` или большому каталогу — да. Ограничьте path при rebuild (`assets/images/catalog`) или используйте cron с `imageoptimizer_cron_limit`.

### Очередь сама не обрабатывается?

Worker не стартует от загрузки файла или rebuild. Варианты:

1. **Обработать очередь** в админке — батчи до `pending = 0`, можно **Остановить** ([manager-guide.md](manager-guide.md))
2. Cron: `cron/convert.php` (фон без открытой вкладки)
3. CLI: `cli/convert.php --limit=N`

### Нужно ли жать «Обработать» много раз?

С версии 1.0.4 — нет. Одна кнопка крутит батчи, пока очередь не опустеет или вы не нажмёте **Остановить**. При лимите времени PHP дожмите cron или нажмите снова.

### Rebuild по папке показывает 0?

Путь **относительно корня media source** (basePath), без ведущего `/`. Пример MS3: `images/resources`, не `/images/resources`. Dry-run считает файлы. Строк очереди будет больше (breakpoints × formats).

### Почему статус skipped?

Частые причины: `upscale=0` (картинка меньше breakpoint), preflight (SVG, animated GIF, HEIC без декодера), MemoryLimit.

### Failed: «Unable to create cache directory»

Обновите пакет: `convert.php` создаёт `core/cache/imageoptimizer/` перед копированием. Либо вручную: `mkdir core/cache/imageoptimizer && chmod 775`.

### Как переконвертировать после смены quality?

Удалите варианты `*.webp` / `*.avif` на диске, удалите записи `done` или сделайте rebuild по path — задачи создадутся заново.

## Фронтенд

### `<picture>` не появляется

1. `imageoptimizer_enabled` и `imageoptimizer_inject_frontend` = 1
2. Плагин активен, событие `OnWebPagePrerender`
3. Путь `<img src>` резолвится в media source (локальный `assets/...`)
4. На диске есть готовые варианты (status `done`)
5. Очистите кэш MODX и HTML-кэш ImageOptimizer

Подробнее: [troubleshooting.md](troubleshooting.md).

### Кириллица превратилась в `&#1044;` после inject

Обновите до 1.0.4+. Старый HTML-кэш удалите: **Очистить кэш** MODX или `queue/clear`. Подробнее: [frontend-guide.md](frontend-guide.md).

### Как отключить для одной картинки?

`class="no-optim"` (из `skip_classes`), атрибут `data-imageoptimizer-skip` или настройка `skip_classes`.

### Уже есть srcset на img

ImageOptimizer не перезаписывает ваш srcset, может добавить lazy/decoding. Для полного `<picture>` уберите ручной srcset или используйте skip.

### Inject не кэшируется для авторизованных

Так задумано: HTML-кэш inject отключён для залогиненных на фронте. Проверяйте страницу в режиме инкогнито или после выхода из аккаунта.

## Админка

### Белый экран / VueTools

Установите **VueTools ≥ 1.2.0-pl**, выполните `npm run build:mgr`, очистите кэш браузера.

### Нет пункта меню

Проверьте ACL: группа должна иметь `imageoptimizer_view`. Переустановите пакет или вручную добавьте права ([permissions.md](permissions.md)).

### Ошибка JSON в Overview («Unexpected token '<'»)

Коннектор вернул HTML. Обновите страницу, проверьте Network → `connector.php`, пересоберите админку (`npm run build:mgr`). См. [troubleshooting.md](troubleshooting.md).

### Live не обрабатывает очередь

**Live** только обновляет цифры на экране. Фоновая обработка — cron (команда на вкладке **Server**).

### Server: все энкодеры «Не найден» (macOS Valet)

FPM часто без imagick и с пустым PATH. CLI и FPM должны быть одной версии PHP. [server-requirements.md](server-requirements.md).

## Производительность

### Нагрузка на cron

Уменьшите `imageoptimizer_cron_limit`. Конвертируйте ночью большими `--limit` через CLI.

### Размер диска

Каждый breakpoint × format — отдельный файл. Для каталога из 10k фото × 5 breakpoints × 2 formats — планируйте место или сократите breakpoints/formats.

## Связанные документы

- [troubleshooting.md](troubleshooting.md) — пошаговая диагностика
- [configuration.md](configuration.md) — все настройки
- [frontend-guide.md](frontend-guide.md) — разметка и inject
