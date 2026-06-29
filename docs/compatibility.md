# Совместимость

ImageOptimizer рассчитан на типичный стек MODX 3: Filesystem media source, pdoTools, VueTools, опционально MiniShop3 и генераторы превью.

## Сводная таблица

| Пакет / компонент | Совместимость | Рекомендация |
|-------------------|---------------|--------------|
| **MODX 3** | Полная | MODX 2.x не поддерживается |
| **pdoTools** | Зависимость install | Установить до или вместе с ImageOptimizer |
| **VueTools 1.1.2+** | Обязателен для админки | Без него — сообщение `vuetools_required` |
| **MiniShop3** | Инъекция на витрине | Чанки товаров не обязаны содержать `<picture>` |
| **Thumb3x** | Skip по URL | `skip_src_pattern=thumb3x` (default) |
| **pThumb / phpThumbOf** | Параллельно | Не конфликтует; разные URL и файлы на диске |
| **Gallery / MIGX** | Зависит от разметки | Локальные `assets/` img — inject; внешние URL — skip |
| **Static HTML cache (MODX)** | Совместимо | Очищайте кэш после bulk convert; учитывайте `html_cache` ImageOptimizer |

## Thumb3x

Thumb3x генерирует on-the-fly URL с сегментом `thumb3x`. ImageOptimizer **не оборачивает** такие `<img>`, чтобы не ломать цепочку pThumb и не дублировать responsive.

Если нужна оптимизация Thumb3x-картинок — используйте либо Thumb3x WebP, либо отключите thumb для статических `<img src="assets/...">` и отдайте их ImageOptimizer.

Настройка: `imageoptimizer_skip_src_pattern` — подстрока в `src` (не regex).

## pThumb / phpThumbOf

Генерируют производные файлы в cache-каталогах по query-параметрам. ImageOptimizer работает с **исходниками** в media source и статическими именами `{basename}.{width}.webp`.

Конфликта имён нет, если не менять `variant_pattern` на шаблон, совпадающий с pThumb.

## MiniShop3

Типичная схема:

1. В чанке товара — `<img src="[[+thumb]]" alt="[[+pagetitle]]">`
2. При upload в MS3 / File Manager — очередь ImageOptimizer
3. На `OnWebPagePrerender` — `<picture>` с WebP srcset

Плагины MS3 для галереи не требуют правки, если итоговый HTML содержит обычные `<img>` с путями в `assets/`.

## VueTools и PrimeVue

Админка собрана Vite + Vue 3 + PrimeVue 4. VueTools подключает Pinia и общий shell менеджера.

После обновления VueTools:

```bash
npm run build:mgr
```

Проверка: **ImageOptimizer → Compatibility** (`action=compatibility/list`).

## MODX 3 Media Sources

Поддерживаются **Filesystem** sources. S3/ FTP без локального пути — preflight `NonFilesystemSource`, задачи skipped.

На MODX 3 используется fallback `sources.modMediaSource` при резолве source для inject.

## Email / формы

`imageoptimizer_inject_email=0` по умолчанию. Многие почтовые клиенты плохо поддерживают `<picture>` — включайте только осознанно.

## Проверка в менеджере

Вкладка **Compatibility**:

- статус VueTools
- подсказки по Thumb3x / MS3
- версии связанных extras (если установлены)

Connector: `action=compatibility/list`.

## Связанные документы

- [frontend-guide.md](frontend-guide.md) — skip и inject
- [configuration.md](configuration.md) — `skip_src_pattern`
- [server-requirements.md](server-requirements.md) — VueTools
