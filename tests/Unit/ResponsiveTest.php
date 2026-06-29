<?php

declare(strict_types=1);

final class ResponsiveTest extends PHPUnit\Framework\TestCase
{
    public function test_build_variant_path_width_zero(): void
    {
        $path = imageoptimizer_build_variant_path('photos/cat.jpg', 0, 'webp', '{basename}.{width}.{ext}');
        $this->assertSame('photos/cat.jpg.webp', $path);
    }

    public function test_build_srcset(): void
    {
        $srcset = imageoptimizer_build_srcset([
            480 => '/assets/a.webp',
            768 => '/assets/b.webp',
        ]);
        $this->assertSame('/assets/a.webp 480w, /assets/b.webp 768w', $srcset);
    }

    public function test_build_srcset_full_width_only(): void
    {
        $srcset = imageoptimizer_build_srcset([
            0 => '/assets/a.webp',
        ]);
        $this->assertSame('/assets/a.webp', $srcset);
    }

    public function test_resolve_sizes_prefers_img_attribute(): void
    {
        $sizes = imageoptimizer_resolve_sizes('', '(max-width: 768px) 100vw, 33vw', '(min-width: 1280px) 50vw, 100vw');
        $this->assertSame('(max-width: 768px) 100vw, 33vw', $sizes);
    }

    public function test_normalize_rejects_traversal(): void
    {
        $this->assertSame('', imageoptimizer_normalize_relative_path('../etc/passwd'));
        $this->assertSame('images/a.jpg', imageoptimizer_normalize_relative_path('images/a.jpg'));
    }

    public function test_variant_pattern_validation(): void
    {
        $this->assertTrue(imageoptimizer_is_valid_variant_pattern('{basename}.{width}.{ext}'));
        $this->assertFalse(imageoptimizer_is_valid_variant_pattern('../evil.{width}.{ext}'));
        $this->assertFalse(imageoptimizer_is_valid_variant_pattern(''));
    }
}
