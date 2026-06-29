<?php

declare(strict_types=1);

final class ImgSkipRulesTest extends PHPUnit\Framework\TestCase
{
    public function test_skips_thumb3x_src_pattern(): void
    {
        $modx = io_create_test_modx(['skip_src_pattern' => 'thumb3x']);
        $rules = new ImageOptimizerImgSkipRules($modx);
        $img = io_img_from_markup('<img src="/assets/thumb3x/item.jpg" alt="x">');

        $this->assertSame(SkipReason::SrcPattern, $rules->shouldSkip($img));
    }

    public function test_skips_data_src_when_matching_pattern(): void
    {
        $modx = io_create_test_modx(['skip_src_pattern' => 'thumb3x']);
        $rules = new ImageOptimizerImgSkipRules($modx);
        $img = io_img_from_markup('<img data-src="/cache/thumb3x/foo.jpg" alt="x">');

        $this->assertSame(SkipReason::SrcPattern, $rules->shouldSkip($img));
    }

    public function test_skips_configured_class(): void
    {
        $modx = io_create_test_modx(['skip_classes' => 'lazy,no-optim']);
        $rules = new ImageOptimizerImgSkipRules($modx);
        $img = io_img_from_markup('<img class="card no-optim" src="/a.jpg" alt="x">');

        $this->assertSame(SkipReason::SkipClass, $rules->shouldSkip($img));
    }

    public function test_skips_data_imageoptimizer_skip(): void
    {
        $modx = io_create_test_modx();
        $rules = new ImageOptimizerImgSkipRules($modx);
        $img = io_img_from_markup('<img data-imageoptimizer-skip src="/a.jpg" alt="x">');

        $this->assertSame(SkipReason::DataSkip, $rules->shouldSkip($img));
    }

    public function test_skips_img_inside_picture_when_respect_enabled(): void
    {
        $modx = io_create_test_modx(['respect_existing_picture' => true]);
        $rules = new ImageOptimizerImgSkipRules($modx);
        $doc = new DOMDocument();
        $doc->loadHTML(
            '<?xml encoding="UTF-8"><html><body><picture><img src="/a.jpg" alt="x"></picture></body></html>',
            LIBXML_NOERROR | LIBXML_NOWARNING
        );
        $img = $doc->getElementsByTagName('img')->item(0);
        $this->assertInstanceOf(DOMElement::class, $img);

        $this->assertSame(SkipReason::ParentIsPicture, $rules->shouldSkip($img));
    }

    public function test_does_not_skip_picture_when_respect_disabled(): void
    {
        $modx = io_create_test_modx(['respect_existing_picture' => false]);
        $rules = new ImageOptimizerImgSkipRules($modx);
        $doc = new DOMDocument();
        $doc->loadHTML(
            '<?xml encoding="UTF-8"><html><body><picture><img src="/a.jpg" alt="x"></picture></body></html>',
            LIBXML_NOERROR | LIBXML_NOWARNING
        );
        $img = $doc->getElementsByTagName('img')->item(0);
        $this->assertInstanceOf(DOMElement::class, $img);

        $this->assertNull($rules->shouldSkip($img));
    }

    public function test_skips_existing_srcset_when_respect_enabled(): void
    {
        $modx = io_create_test_modx(['respect_existing_srcset' => true]);
        $rules = new ImageOptimizerImgSkipRules($modx);
        $img = io_img_from_markup('<img src="/a.jpg" srcset="/a.jpg 1w" alt="x">');

        $this->assertSame(SkipReason::HasSrcset, $rules->shouldSkip($img));
    }

    public function test_apply_lazy_makes_first_image_eager_when_configured(): void
    {
        $modx = io_create_test_modx([
            'skip_lazy_first_images' => 1,
            'respect_existing_loading' => false,
        ]);
        $rules = new ImageOptimizerImgSkipRules($modx);
        $first = io_img_from_markup('<img src="/a.jpg" alt="first">');
        $second = io_img_from_markup('<img src="/b.jpg" alt="second">');

        $rules->applyLazyAttributes($first, 0);
        $rules->applyLazyAttributes($second, 1);

        $this->assertSame('eager', $first->getAttribute('loading'));
        $this->assertSame('lazy', $second->getAttribute('loading'));
        $this->assertSame('async', $first->getAttribute('decoding'));
    }

    public function test_apply_lazy_respects_existing_loading_attribute(): void
    {
        $modx = io_create_test_modx(['respect_existing_loading' => true]);
        $rules = new ImageOptimizerImgSkipRules($modx);
        $img = io_img_from_markup('<img src="/a.jpg" loading="eager" alt="x">');

        $rules->applyLazyAttributes($img, 5);

        $this->assertSame('eager', $img->getAttribute('loading'));
    }

    public function test_fixture_skip_thumb3x_matches_rules(): void
    {
        $modx = io_create_test_modx();
        $rules = new ImageOptimizerImgSkipRules($modx);
        $img = io_img_from_markup(io_fixture_html('skip_thumb3x.html'));

        $this->assertSame(SkipReason::SrcPattern, $rules->shouldSkip($img));
    }
}
