<?php

declare(strict_types=1);

final class HtmlCacheTest extends PHPUnit\Framework\TestCase
{
    private string $cacheRoot;

    public function setUp(): void
    {
        $this->cacheRoot = sys_get_temp_dir() . '/imageoptimizer-htmlcache-' . uniqid('', true) . '/';
        mkdir($this->cacheRoot, 0755, true);
    }

    public function tearDown(): void
    {
        if (is_dir($this->cacheRoot)) {
            foreach (glob($this->cacheRoot . '**/*', GLOB_NOSORT) ?: [] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            foreach (glob($this->cacheRoot . '*', GLOB_ONLYDIR) ?: [] as $dir) {
                @rmdir($dir);
            }
            @rmdir($this->cacheRoot);
        }
    }

    public function test_bump_increments_generation(): void
    {
        $modx = io_create_test_modx();
        $modx->setOption('imageoptimizer.cache_path', $this->cacheRoot);

        $this->assertSame(0, imageoptimizer_html_cache_generation($modx));
        imageoptimizer_bump_html_cache_generation($modx);
        $this->assertSame(1, imageoptimizer_html_cache_generation($modx));
        imageoptimizer_bump_html_cache_generation($modx);
        $this->assertSame(2, imageoptimizer_html_cache_generation($modx));
    }

    public function test_cache_file_key_changes_after_bump(): void
    {
        $modx = io_create_test_modx();
        $modx->setOption('imageoptimizer.cache_path', $this->cacheRoot);
        $modx->resource = new modResource();
        $modx->resource->set('uri', 'catalog/');
        $modx->resource->set('editedon', '2026-01-01 00:00:00');

        $before = imageoptimizer_html_cache_file($modx, 'hash-a');
        imageoptimizer_bump_html_cache_generation($modx);
        $after = imageoptimizer_html_cache_file($modx, 'hash-a');

        $this->assertNotNull($before);
        $this->assertNotNull($after);
        $this->assertNotSame($before, $after);
    }

    public function test_cache_file_key_changes_when_html_content_hash_changes(): void
    {
        $modx = io_create_test_modx();
        $modx->setOption('imageoptimizer.cache_path', $this->cacheRoot);
        $modx->resource = new modResource();
        $modx->resource->set('uri', 'ms3-delivery-widgets-test.html');
        $modx->resource->set('editedon', '2026-01-01 00:00:00');

        $a = imageoptimizer_html_cache_file($modx, md5('<p>old</p>'));
        $b = imageoptimizer_html_cache_file($modx, md5('<p>new from fenom file</p>'));

        $this->assertNotNull($a);
        $this->assertNotNull($b);
        $this->assertNotSame($a, $b);
    }

    public function test_clear_html_cache_resets_generation(): void
    {
        $modx = io_create_test_modx();
        $modx->setOption('imageoptimizer.cache_path', $this->cacheRoot);
        $htmlDir = $this->cacheRoot . 'html/';
        mkdir($htmlDir, 0755, true);
        file_put_contents($htmlDir . 'page.html', '<html></html>');
        imageoptimizer_bump_html_cache_generation($modx);

        imageoptimizer_clear_html_cache($modx);

        $this->assertSame(0, imageoptimizer_html_cache_generation($modx));
        $this->assertFileDoesNotExist($htmlDir . 'page.html');
    }
}
