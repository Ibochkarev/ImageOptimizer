<?php

declare(strict_types=1);

final class InjectTest extends PHPUnit\Framework\TestCase
{
    private ?string $previousXhr = null;

    public function setUp(): void
    {
        $this->previousXhr = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? null;
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    public function tearDown(): void
    {
        if ($this->previousXhr !== null) {
            $_SERVER['HTTP_X_REQUESTED_WITH'] = $this->previousXhr;
        } else {
            unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        }
    }

    public function test_inject_disabled_leaves_html_untouched(): void
    {
        $modx = io_create_test_modx(['inject_frontend' => false]);
        $html = '<img src="/assets/a.jpg" alt="x">';
        $original = $html;

        imageoptimizer_inject_html($modx, $html);

        $this->assertSame($original, $html);
    }

    public function test_inject_skips_mgr_context(): void
    {
        $modx = io_create_test_modx();
        $modx->context = new modContext('mgr');
        $html = '<img src="/assets/a.jpg" alt="x">';
        $original = $html;

        imageoptimizer_inject_html($modx, $html);

        $this->assertSame($original, $html);
    }

    public function test_inject_skips_ajax_requests(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $modx = io_create_test_modx();
        $html = '<img src="/assets/a.jpg" alt="x">';
        $original = $html;

        imageoptimizer_inject_html($modx, $html);

        $this->assertSame($original, $html);
    }

    public function test_inject_skips_non_html_content_type(): void
    {
        $modx = new ModxInjectTestDouble('web');
        $modx->setOption('imageoptimizer_html_cache', '0');
        $modx->setOption('imageoptimizer_inject_frontend', '1');

        $resource = new modResource();
        $resource->set('content_type', 2);
        $modx->resource = $resource;

        $contentType = new modContentType();
        $contentType->set('mime_type', 'application/json');
        $modx->contentTypeObject = $contentType;

        $html = '{"image":"/assets/a.jpg"}';
        $original = $html;

        imageoptimizer_inject_html($modx, $html);

        $this->assertSame($original, $html);
    }

    public function test_inject_skips_when_html_exceeds_max_size(): void
    {
        $modx = io_create_test_modx(['max_html_size' => 20]);
        $html = '<img src="/assets/very-long-path.jpg" alt="x">';

        imageoptimizer_inject_html($modx, $html);

        $this->assertStringNotContainsString('loading=', $html);
        $this->assertStringNotContainsString('<picture', $html);
    }

    public function test_fixture_skip_thumb3x_does_not_wrap_picture(): void
    {
        $modx = io_create_test_modx();
        $html = io_fixture_html('skip_thumb3x.html');

        imageoptimizer_inject_html($modx, $html);

        $this->assertStringNotContainsString('<picture', $html);
        $this->assertStringContainsString('thumb3x', $html);
    }

    public function test_fixture_skip_class_does_not_wrap_picture(): void
    {
        $modx = io_create_test_modx();
        $html = io_fixture_html('skip_class.html');

        imageoptimizer_inject_html($modx, $html);

        $this->assertStringNotContainsString('<picture', $html);
        $this->assertStringContainsString('no-optim', $html);
    }

    public function test_fixture_skip_data_attr_does_not_wrap_picture(): void
    {
        $modx = io_create_test_modx();
        $html = io_fixture_html('skip_data_attr.html');

        imageoptimizer_inject_html($modx, $html);

        $this->assertStringNotContainsString('<picture', $html);
        $this->assertStringContainsString('data-imageoptimizer-skip', $html);
    }

    public function test_fixture_existing_picture_not_doubled(): void
    {
        $modx = io_create_test_modx();
        $html = io_fixture_html('existing_picture.html');

        imageoptimizer_inject_html($modx, $html);

        $this->assertSame(1, substr_count(strtolower($html), '<picture'));
    }

    public function test_fixture_existing_srcset_adds_lazy_loading(): void
    {
        $modx = io_create_test_modx([
            'respect_existing_srcset' => true,
            'respect_existing_loading' => false,
        ]);
        $html = io_fixture_html('existing_srcset.html');

        imageoptimizer_inject_html($modx, $html);

        $this->assertStringNotContainsString('<picture', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringContainsString('srcset=', $html);
    }

    public function test_settings_hash_changes_when_skip_pattern_changes(): void
    {
        $modxA = io_create_test_modx(['skip_src_pattern' => 'thumb3x']);
        $modxB = io_create_test_modx(['skip_src_pattern' => 'other']);

        $this->assertNotSame(
            imageoptimizer_settings_hash($modxA),
            imageoptimizer_settings_hash($modxB)
        );
    }
}
