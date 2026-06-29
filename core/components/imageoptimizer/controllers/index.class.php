<?php

/**
 * Контроллер админки ImageOptimizer (Vue 3 + PrimeVue через VueTools).
 *
 * @package imageoptimizer
 */
class ImageOptimizerIndexManagerController extends modManagerController
{
    protected static $vueCoreCheckRegistered = false;

    public function checkPermissions(): bool
    {
        return $this->modx->hasPermission('imageoptimizer_view');
    }

    public function getPageTitle(): string
    {
        require_once $this->modx->getOption('core_path') . 'components/imageoptimizer/include/paths.php';
        require_once imageoptimizer_core_path($this->modx) . 'include/lexicon.php';
        imageoptimizer_load_lexicon($this->modx, 'default');

        return $this->modx->lexicon('imageoptimizer.desc') ?: ($this->modx->lexicon('imageoptimizer') ?: 'ImageOptimizer');
    }

    protected function getFrontendAssetsVersion(): string
    {
        $files = [
            'components/imageoptimizer/js/mgr/vue-dist/imageoptimizer-admin.min.js',
            'components/imageoptimizer/js/mgr/vue-dist/imageoptimizer-admin.min.css',
            'components/imageoptimizer/css/mgr/main.css',
        ];
        $maxMtime = 0;
        $assetsPath = $this->modx->getOption('assets_path', null, '');
        if ($assetsPath !== '') {
            $base = rtrim($assetsPath, '/') . '/';
            foreach ($files as $rel) {
                $path = $base . $rel;
                if (is_file($path)) {
                    $maxMtime = max($maxMtime, filemtime($path));
                }
            }
        }

        return $maxMtime > 0 ? (string) $maxMtime : (string) ($this->modx->getVersionData()['full_version'] ?? '1');
    }

    protected function getAssetsBaseUrl(): string
    {
        require_once $this->modx->getOption('core_path') . 'components/imageoptimizer/include/paths.php';

        return imageoptimizer_assets_base_url($this->modx);
    }

    public function loadCustomCssJs(): void
    {
        $assetsUrl = $this->getAssetsBaseUrl();
        $connectorUrl = $assetsUrl . 'connector.php';
        require_once imageoptimizer_core_path($this->modx) . 'include/lexicon.php';
        $lexicon = imageoptimizer_build_mgr_lexicon($this->modx);

        $mgrLang = (string) $this->modx->getOption('manager_language', null, $this->modx->getOption('cultureKey', null, 'en'));

        $config = [
            'connectorUrl' => $connectorUrl,
            'modAuth' => (string) $this->modx->user->getUserToken('mgr'),
            'lexicon' => $lexicon,
            'managerLanguage' => $mgrLang,
            'permissions' => [
                'view' => (int) $this->modx->hasPermission('imageoptimizer_view'),
                'settings' => (int) $this->modx->hasPermission('imageoptimizer_settings'),
                'run' => (int) $this->modx->hasPermission('imageoptimizer_run'),
            ],
        ];
        $version = $this->getFrontendAssetsVersion();
        $this->modx->regClientStartupHTMLBlock(
            '<script>window.imageoptimizerConfig = ' . json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';'
            . 'if (window.imageoptimizerAdmin && typeof window.imageoptimizerAdmin.bootstrap === "function") { window.imageoptimizerAdmin.bootstrap(); }</script>'
        );

        if (!self::$vueCoreCheckRegistered) {
            $this->registerVueCoreCheck();
            self::$vueCoreCheckRegistered = true;
        }

        $vueDistUrl = $assetsUrl . 'js/mgr/vue-dist/imageoptimizer-admin.min.js?v=' . $version;
        $this->addCss($assetsUrl . 'js/mgr/vue-dist/imageoptimizer-admin.min.css?v=' . $version);
        $this->addCss($assetsUrl . 'css/mgr/main.css?v=' . $version);
        $this->modx->regClientStartupHTMLBlock(
            '<script type="module" data-vue-module src="' . htmlspecialchars($vueDistUrl, ENT_QUOTES, 'UTF-8') . '"></script>'
        );
    }

    protected function registerVueCoreCheck(): void
    {
        $title = addslashes($this->modx->lexicon('imageoptimizer') ?: 'ImageOptimizer');
        $message = addslashes($this->modx->lexicon('imageoptimizer_vuetools_required')
            ?: 'Для работы ImageOptimizer требуется пакет VueTools 1.1.2+. Установите его через Менеджер пакетов.');
        $script = <<<JS
<script>
(function(){
  const importMap = document.querySelector('script[type="importmap"]');
  let hasVueCore = false;
  if (importMap) {
    try {
      const mapContent = JSON.parse(importMap.textContent);
      hasVueCore = mapContent.imports && mapContent.imports.vue;
    } catch (e) { hasVueCore = false; }
  }
  if (!hasVueCore) {
    document.querySelectorAll('script[type="module"][data-vue-module]').forEach(function(el) { el.remove(); });
    if (typeof Ext !== 'undefined') {
      Ext.onReady(function() {
        if (typeof MODx !== 'undefined' && MODx.msg) {
          MODx.msg.alert('{$title}', '{$message}');
        } else { alert('{$message}'); }
      });
    } else {
      document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
          if (typeof MODx !== 'undefined' && MODx.msg) {
            MODx.msg.alert('{$title}', '{$message}');
          } else { alert('{$message}'); }
        }, 500);
      });
    }
    window.IMAGEOPTIMIZER_VUE_CORE_MISSING = true;
  }
})();
</script>
JS;
        $this->modx->regClientStartupHTMLBlock($script);
    }

    public function process(array $scriptProperties = []): array
    {
        return [];
    }

    public function getTemplateFile(): string
    {
        require_once $this->modx->getOption('core_path') . 'components/imageoptimizer/include/paths.php';

        return imageoptimizer_core_path($this->modx) . 'templates/default/index.tpl';
    }
}
