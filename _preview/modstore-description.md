<strong>ImageOptimizer</strong> ускоряет сайт на MODX 3 без правки чанков: конвертирует загруженные JPEG и PNG в <strong>WebP</strong> (и опционально <strong>AVIF</strong>), создаёт responsive-варианты и на выходе страницы подставляет <code>&lt;picture&gt;</code> с <code>srcset</code>. Браузер получает меньший файл, оригиналы остаются fallback.

<strong>Бесплатно.</strong> MiniShop3 не обязателен — работает на любом HTML-сайте MODX, включая витрину MS3 и каталоги в File Manager.

<strong>Что получаете после установки</strong>
<ul>
  <li><strong>Меньший вес картинок</strong> — статические WebP/AVIF рядом с оригиналом (<code>photo.768.webp</code>), отдаются как обычные файлы, без PHP на каждый запрос</li>
  <li><strong>Responsive из коробки</strong> — breakpoints 480–1920 px (настраиваются), <code>srcset</code> и <code>sizes</code> в разметке</li>
  <li><strong>Авто-<code>&lt;picture&gt;</code></strong> — плагин на <code>OnWebPagePrerender</code> оборачивает локальные <code>&lt;img&gt;</code>, шаблоны и чанки не трогаете</li>
  <li><strong>Очередь под контролем</strong> — Vue-админка: статистика, фильтры, retry, rebuild по файлу или папке, кнопка <strong>Обработать очередь</strong> (cron не обязателен для старта)</li>
  <li><strong>Массовая обработка архива</strong> — пересборка каталога товаров (<code>assets/images/products</code>), загрузок MS3 (<code>images/resources</code>) или любого path в media source</li>
  <li><strong>Умные пропуски</strong> — Thumb3x/pThumb, SVG, внешние URL, класс <code>no-optim</code>, атрибут <code>data-imageoptimizer-skip</code>, уже готовый <code>srcset</code> или <code>&lt;picture&gt;</code></li>
</ul>

<strong>Три шага до результата</strong>
<ol>
  <li>Установите пакет, <strong>pdoTools</strong> и <strong>VueTools</strong> (≥ 1.1.2-pl). На вкладке <strong>Сервер</strong> проверьте, что WebP «Доступен».</li>
  <li>Загрузите изображение в File Manager <em>или</em> нажмите <strong>Пересобрать очередь</strong> по нужной папке → <strong>Обработать очередь</strong>.</li>
  <li>Откройте страницу с картинкой — в HTML появится <code>&lt;picture&gt;</code> с WebP source (при <code>inject_frontend=1</code> и активном плагине).</li>
</ol>

<strong>Для кого</strong>
<ul>
  <li><strong>Контент-сайты и лендинги</strong> — ускорение без ручной верстки picture</li>
  <li><strong>Интернет-магазины на MS3</strong> — галереи товаров и загрузки resources</li>
  <li><strong>Разработчики</strong> — CLI/cron (<code>cli/convert.php</code>, <code>cron/convert.php</code>), права ACL, HTML-кэш инъекции, QA-стенд с 20 секциями тестов</li>
</ul>

<strong>Требования:</strong> MODX <strong>3.0+</strong>, PHP <strong>8.2+</strong>, GD или Imagick с WebP. AVIF — по желанию (<code>cwebp</code>, <code>avifenc</code>).

<strong>Документация:</strong> <a href="https://docs.modx.pro/components/imageoptimizer/" target="_blank" rel="noopener noreferrer">docs.modx.pro/components/imageoptimizer/</a> — установка, настройки, админка, фронт, CLI, FAQ.

<strong>Скриншоты</strong>
<p><em>Загрузите PNG в карточку ModStore или подставьте URL после публикации. Исходники — в <code>_preview/</code>.</em></p>
<p><img src="screenshot-01-overview.png" alt="ImageOptimizer — вкладка «Обзор»: статистика очереди и готовность сервера" width="960" /></p>
<p><img src="screenshot-02-queue.png" alt="ImageOptimizer — вкладка «Очередь»: таблица задач и кнопка «Обработать очередь»" width="960" /></p>
<p><img src="screenshot-03-server.png" alt="ImageOptimizer — вкладка «Сервер»: энкодеры WebP и AVIF" width="960" /></p>
<p><img src="screenshot-04-picture.png" alt="ImageOptimizer — &lt;picture&gt; с WebP source в HTML страницы" width="960" /></p>
