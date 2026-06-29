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
}
