<?php

declare(strict_types=1);

final class HtmlParserTest extends PHPUnit\Framework\TestCase
{
    public function test_normalize_removes_stray_source_closing_before_picture(): void
    {
        $broken = '<picture><source srcset="/a.jpg" type="image/jpeg"><img src="/a.jpg" alt=""></source></picture>';
        $fixed = imageoptimizer_normalize_html_output($broken);

        $this->assertStringNotContainsString('</source></picture>', $fixed);
        $this->assertStringContainsString('<picture>', $fixed);
    }

    public function test_serialize_existing_picture_keeps_valid_markup(): void
    {
        $html = <<<'HTML'
<picture data-io-case="existing-picture">
    <source srcset="/assets/test/hero.jpg" type="image/jpeg">
    <img src="/assets/test/hero.jpg" alt="Manual picture">
</picture>
HTML;
        $doc = imageoptimizer_load_html_document($html);
        $this->assertNotNull($doc);
        $out = imageoptimizer_serialize_document($doc);

        $this->assertSame(1, substr_count(strtolower($out), '<picture'));
        $this->assertStringNotContainsString('</source></picture>', $out);
    }

    public function test_extract_restore_preserves_script_with_html_looking_strings(): void
    {
        $html = <<<'HTML'
<p>before</p>
<script>
wrap.innerHTML = '<strong class="x">Smoke</strong><div id="ms3dw-test-smoke-delivery-radios"></div>';
</script>
<img src="/assets/a.jpg" alt="x">
HTML;
        [$forDom, $blocks] = imageoptimizer_extract_raw_blocks($html);
        $this->assertStringNotContainsString('<script>', $forDom);
        $this->assertCount(1, $blocks);

        $doc = imageoptimizer_load_html_document($forDom);
        $this->assertNotNull($doc);
        $out = imageoptimizer_restore_raw_blocks(imageoptimizer_serialize_document($doc), $blocks);

        $this->assertStringContainsString(
            "wrap.innerHTML = '<strong class=\"x\">Smoke</strong><div id=\"ms3dw-test-smoke-delivery-radios\"></div>';",
            $out
        );
        $this->assertStringNotContainsString('</script></div>', $out);
    }

    public function test_full_document_preserves_html_and_head(): void
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>IO full page</title>
    <link rel="stylesheet" href="/assets/site.css">
</head>
<body>
    <h1>Hello</h1>
    <img src="/assets/a.jpg" alt="x">
</body>
</html>
HTML;
        $this->assertTrue(imageoptimizer_is_full_html_document($html));

        $doc = imageoptimizer_load_html_document($html);
        $this->assertNotNull($doc);
        $out = imageoptimizer_serialize_document($doc);

        $this->assertMatchesRegularExpression('/<html\b/i', $out);
        $this->assertMatchesRegularExpression('/<head\b/i', $out);
        $this->assertMatchesRegularExpression('/<body\b/i', $out);
        $this->assertStringContainsString('IO full page', $out);
        $this->assertStringContainsString('/assets/site.css', $out);
        $this->assertStringContainsString('<img ', $out);
        $this->assertStringNotContainsString('imageoptimizer-root', $out);
        $this->assertStringNotContainsString('<?xml', $out);
        $this->assertStringNotContainsString('<!--?xml', $out);
        $this->assertDoesNotMatchRegularExpression('/<!--\s*\?xml/i', $out);
        $this->assertStringStartsWith('<!DOCTYPE html>', ltrim($out));
    }

    public function test_normalize_strips_cached_xml_encoding_comment(): void
    {
        $dirty = "<!DOCTYPE html>\n<!--?xml encoding=\"UTF-8\"-->\n<html><head></head><body></body></html>";
        $out = imageoptimizer_normalize_html_output($dirty);

        $this->assertStringNotContainsString('<!--?xml', $out);
        $this->assertStringContainsString('<html>', $out);
    }

    public function test_fragment_serialize_does_not_emit_document_shell(): void
    {
        $html = '<p>frag</p><img src="/assets/a.jpg" alt="x">';
        $this->assertFalse(imageoptimizer_is_full_html_document($html));

        $doc = imageoptimizer_load_html_document($html);
        $this->assertNotNull($doc);
        $out = imageoptimizer_serialize_document($doc);

        $this->assertStringContainsString('<p>frag</p>', $out);
        $this->assertStringContainsString('<img ', $out);
        $this->assertDoesNotMatchRegularExpression('/^\s*<!DOCTYPE/i', $out);
        $this->assertDoesNotMatchRegularExpression('/^\s*<html\b/i', $out);
        $this->assertStringNotContainsString('imageoptimizer-root', $out);
    }
}
