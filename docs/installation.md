# Установка ImageOptimizer

## Требования

| Компонент | Версия | Назначение |
|-----------|--------|------------|
| MODX Revolution | ≥ 3.0.0 | Платформа |
| PHP | ≥ 8.2.0 | Runtime (enum, readonly) |
| [pdoTools](https://modx.pro/components/pdotools) | актуальная | Зависимость transport (resolver) |
| [VueTools](https://docs.modx.pro/components/vuetools/) | ≥ 1.1.2-pl | Админка (Vue 3 + PrimeVue) |
| GD **или** Imagick | с WebP | Минимум для конвертации |
| MiniShop3 | опционально | Витрина. Инъекция работает на любом HTML-сайте |

Расширения PHP: `fileinfo`, `gd` или `imagick`. Для AVIF — `avifenc` или Imagick с AVIF (см. [server-requirements.md](server-requirements.md)).

## Установка из транспорта

### 1. Сборка пакета

Из корня репозитория ImageOptimizer (нужен установленный MODX — `_build/config.inc.php` находит `core/config/config.inc.php`):

```bash
pnpm install   # или npm install
pnpm run build:mgr
php _build/build.php
```

Архив: `core/packages/imageoptimizer-1.0.4-beta1.transport.zip` (версия из `_build/config.inc.php`).

Скачать через браузер (если `download=1` в config):

```
https://ваш-сайт/Extras/ImageOptimizer/_build/build.php?download=1
```

`build.php` при наличии `package.json` и `vite.mgr.config.js` запускает `npm run build:mgr`.

### 2. Установка в MODX

1. **Пакеты** → загрузить `.transport.zip` или положить в `core/packages/`
2. Установить **ImageOptimizer**
3. Убедиться, что установлены **pdoTools** и **VueTools**
4. Очистить кэш MODX

После install resolver создаёт:

- namespace `imageoptimizer`
- таблицу `imageoptimizer_queue`
- права доступа (Administrator, Manager)
- пункт меню **Компоненты → ImageOptimizer**
- плагин **ImageOptimizer** (события File Manager + `OnWebPagePrerender`)
- системные настройки `imageoptimizer_*`

### 3. Проверка

- Открыть `manager/?a=index&namespace=imageoptimizer` — Vue-админка без ошибки VueTools
- Вкладка **Server** — хотя бы один энкодер WebP «Доступен»
- Загрузить JPEG в File Manager → в очереди появляются задачи (если `convert_on_upload=1`)

## Сборка админки (разработка)

```bash
pnpm install   # или npm install
pnpm run build:mgr
```

Результат: `assets/components/imageoptimizer/js/mgr/vue-dist/imageoptimizer-admin.min.js` (+ `.css`, шрифты PrimeIcons).

Перед transport или деплоем на dev-сайт пересборка обязательна.

## Первый запуск на сайте

1. Включите настройки (по умолчанию уже включены):
   - `imageoptimizer_enabled`
   - `imageoptimizer_inject_frontend`
   - `imageoptimizer_convert_on_upload`
2. Убедитесь, что плагин **ImageOptimizer** активен.
3. Настройте cron **или** обрабатывайте очередь из админки (**Обработать очередь** — батчи до `pending = 0`, см. [manager-guide.md](manager-guide.md)):

```bash
*/10 * * * * php /path/to/core/components/imageoptimizer/cron/convert.php
```

4. Для существующего каталога — **Очередь → Пересобрать** с путём **файла или папки** относительно source (например `assets/images/catalog`, `images/resources`) или CLI:

```bash
php core/components/imageoptimizer/cli/convert.php --source=1 --scan --path=assets/images --limit=500
```

5. Нажмите **Обработать очередь** или дождитесь cron. Очистите кэш MODX после первой массовой конвертации.

Подробнее о настройках: [configuration.md](configuration.md).

## Проверка после установки

Ручной smoke-чеклист — [testing.md](testing.md). Отдельный seed-скрипт и QA-шаблон в transport не входят: проверяйте на своих страницах с `<img src="assets/...">` или соберите тестовый ресурс вручную.

## Обновление пакета

1. Собрать новый transport zip
2. **Пакеты → ImageOptimizer → Обновить**
3. `npm run build:mgr` уже встроен в transport assets при сборке
4. Очистить кэш

Настройки при update по умолчанию не перезаписываются (`update.settings => false` в config).

## Удаление

При `imageoptimizer_cleanup_on_uninstall=1` uninstall удаляет таблицу очереди, варианты файлов и HTML-кэш. Иначе данные остаются на диске.

## Связанные документы

- [configuration.md](configuration.md) — что настроить после install
- [manager-guide.md](manager-guide.md) — админка
- [cli.md](cli.md) — cron и bulk
