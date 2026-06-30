<p><strong>ImageOptimizer</strong> — бесплатное дополнение для MODX 3, которое убирает рутину вокруг современных форматов изображений. Загружаете JPEG или PNG в File Manager — компонент ставит задачи в очередь, конвертирует в WebP (и при необходимости AVIF), создаёт варианты под разные ширины экрана и на выходе страницы сам оборачивает <code>&lt;img&gt;</code> в <code>&lt;picture&gt;</code>. Чанки, шаблоны и галереи MiniShop3 переписывать не нужно.</p>

<p>Документация: <a href="https://docs.modx.pro/components/imageoptimizer/" target="_blank" rel="noopener noreferrer"><u>docs.modx.pro/components/imageoptimizer/</u></a></p>

<p><img src="screenshot-01-overview.png" alt="ImageOptimizer — вкладка «Обзор»" width="960" /></p>

<cut>

<p><strong>Проблема, которую решает</strong></p>
<p>Тяжёлые JPEG и PNG тянут LCP и PageSpeed. Ручная подготовка WebP, srcset и picture для сотен файлов в каталоге — отдельный проект. On-the-fly ресайзеры вроде Thumb3x удобны для динамики, но каждый размер — запрос к PHP. ImageOptimizer идёт другим путём: <strong>заранее</strong> пишет статические файлы рядом с оригиналом и один раз подставляет готовую разметку при отдаче HTML.</p>

<p><strong>Что меняется на практике</strong></p>
<ul>
  <li>Редактор загружает картинку — она попадает в очередь (<code>convert_on_upload</code>).</li>
  <li>Менеджер жмёт <strong>Обработать очередь</strong> в админке или настраивает cron — на диске появляются <code>*.webp</code> по breakpoints.</li>
  <li>Посетитель открывает страницу — браузер видит <code>&lt;picture&gt;</code> и забирает WebP там, где умеет. Старые браузеры остаются на исходнике.</li>
</ul>
<p>Для уже существующего архива — <strong>Пересобрать очередь</strong> по папке: каталог товаров, <code>images/resources</code> MS3 или один файл. Path указывается относительно media source, не URL сайта.</p>
<p><img src="screenshot-04-picture.png" alt="ImageOptimizer — &lt;picture&gt; с WebP в HTML страницы" width="960" /></p>

<p><strong>Админка на Vue</strong></p>
<p>Компонент <strong>ImageOptimizer</strong> в менеджере: обзор очереди и сервера, таблица задач с фильтрами, все настройки <code>imageoptimizer_*</code>, проверка энкодеров (GD, Imagick, cwebp, avifenc), блок совместимости с Thumb3x, pThumb, MiniShop3 и VueTools. Live-обновление сводки — удобно следить за bulk-конвертацией.</p>
<p><img src="screenshot-02-queue.png" alt="ImageOptimizer — вкладка «Очередь»" width="960" /></p>
<p><img src="screenshot-03-server.png" alt="ImageOptimizer — вкладка «Сервер»" width="960" /></p>

<p><strong>Не ломает то, что уже настроено</strong></p>
<p>URL с <code>thumb3x</code> пропускаются — не конфликтует с on-the-fly thumbs. Уважаются существующие <code>srcset</code>, <code>&lt;picture&gt;</code>, атрибуты <code>loading</code> и <code>decoding</code>. SVG и внешние ссылки не трогаются. Нужно исключить одну картинку — класс из <code>skip_classes</code> или <code>data-imageoptimizer-skip</code>.</p>

<p><strong>MiniShop3 — опционально</strong></p>
<p>Инъекция работает на любом HTML. На витрине MS3 проверены типичные кейсы: товарные JPEG, загрузки в <code>images/resources</code>. Отдельных сниппетов для магазина не требуется.</p>

<p><strong>Для администратора сервера</strong></p>
<ul>
  <li>Worker из админки, <code>php core/components/imageoptimizer/cli/convert.php</code> или cron <code>cron/convert.php</code></li>
  <li>Lock-файл и сброс зависших <code>processing</code></li>
  <li>Опциональный HTML-кэш после инъекции (<code>html_cache</code>)</li>
  <li>Права: <code>imageoptimizer_view</code>, <code>imageoptimizer_settings</code>, <code>imageoptimizer_run</code></li>
</ul>

<p><strong>Установка</strong></p>
<ul>
  <li>MODX Revolution <strong>3.0+</strong>, PHP <strong>8.2+</strong></li>
  <li><strong>pdoTools</strong>, <strong>VueTools</strong> ≥ 1.1.2-pl</li>
  <li>GD или Imagick с поддержкой WebP</li>
</ul>

<p><strong>Быстрый старт</strong></p>
<ol>
  <li>Установите transport через <kbd>Управление пакетами</kbd>.</li>
  <li><strong>ImageOptimizer → Сервер</strong> — WebP «Доступен».</li>
  <li>Загрузите изображение или rebuild по каталогу → <strong>Обработать очередь</strong>.</li>
  <li>View Source страницы — есть <code>&lt;picture&gt;</code> и WebP source.</li>
</ol>

<p>На dev-сайте можно развернуть QA-стенд: seed <code>core/elements/demo/seed_imageoptimizer_demo.php</code>, страница <code>/imageoptimizer-test.html</code> — 20 секций проверки inject, skip и DOM-отчёт.</p>

<p><strong>Скриншоты (заглушки)</strong></p>
<p><em>Файлы положите в <code>_preview/</code>, при публикации загрузите на ModStore / MODX.pro и замените <code>src</code> на URL.</em></p>
<ul>
  <li><code>screenshot-01-overview.png</code> — вкладка «Обзор»</li>
  <li><code>screenshot-02-queue.png</code> — вкладка «Очередь»</li>
  <li><code>screenshot-03-server.png</code> — вкладка «Сервер»</li>
  <li><code>screenshot-04-picture.png</code> — <code>&lt;picture&gt;</code> в HTML (DevTools или View Source)</li>
</ul>

<p><strong>Ссылки</strong></p>
<ul>
  <li><a href="https://docs.modx.pro/components/imageoptimizer/" target="_blank" rel="noopener noreferrer"><u>Документация на MODX.pro</u></a></li>
</ul>

ImageOptimizer — WebP, AVIF и responsive picture для MODX 3
