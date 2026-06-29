<?php

declare(strict_types=1);

final class PreflightTest extends PHPUnit\Framework\TestCase
{
    private string $tempDir;

    public function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/imageoptimizer-preflight-' . uniqid('', true);
        mkdir($this->tempDir, 0755, true);
    }

    public function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            foreach (glob($this->tempDir . '/*') ?: [] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->tempDir);
        }
    }

    public function test_is_image_mime_excludes_svg(): void
    {
        $this->assertTrue(imageoptimizer_is_image_mime('image/jpeg'));
        $this->assertTrue(imageoptimizer_is_image_mime('image/png'));
        $this->assertFalse(imageoptimizer_is_image_mime('image/svg+xml'));
        $this->assertFalse(imageoptimizer_is_image_mime('text/html'));
    }

    public function test_is_filesystem_source(): void
    {
        $fileSource = new modMediaSource();
        $fileSource->set('class_key', 'sources.modFileMediaSource');

        $s3Source = new modMediaSource();
        $s3Source->set('class_key', 'sources.modS3MediaSource');

        $this->assertTrue(imageoptimizer_is_filesystem_source($fileSource));
        $this->assertFalse(imageoptimizer_is_filesystem_source($s3Source));
    }

    public function test_preflight_skips_non_filesystem_source(): void
    {
        $modx = io_create_test_modx();
        $source = new modMediaSource();
        $source->set('class_key', 'sources.modS3MediaSource');
        $path = $this->tempDir . '/plain.jpg';
        file_put_contents($path, 'not-a-real-jpeg');

        $this->assertSame(
            SkipReason::NonFilesystemSource,
            imageoptimizer_preflight($modx, $source, $path, 'image/jpeg')
        );
    }

    public function test_preflight_skips_svg(): void
    {
        $modx = io_create_test_modx();
        $source = $this->filesystemSource();
        $path = $this->tempDir . '/icon.svg';
        file_put_contents($path, '<svg></svg>');

        $this->assertSame(
            SkipReason::SvgSkip,
            imageoptimizer_preflight($modx, $source, $path, 'image/svg+xml')
        );
    }

    public function test_preflight_skips_existing_webp(): void
    {
        $modx = io_create_test_modx();
        $source = $this->filesystemSource();
        $path = $this->tempDir . '/already.webp';
        file_put_contents($path, 'RIFF....WEBP');

        $this->assertSame(
            SkipReason::AlreadyWebp,
            imageoptimizer_preflight($modx, $source, $path, 'image/webp')
        );
    }

    public function test_preflight_skips_animated_gif(): void
    {
        $modx = io_create_test_modx();
        $source = $this->filesystemSource();
        $path = $this->tempDir . '/anim.gif';
        file_put_contents($path, 'GIF89a' . str_repeat("\0", 64) . 'NETSCAPE2.0' . "\0");

        $this->assertTrue(imageoptimizer_is_animated_image($path, 'image/gif'));
        $this->assertSame(
            SkipReason::AnimatedNotSupported,
            imageoptimizer_preflight($modx, $source, $path, 'image/gif')
        );
    }

    public function test_preflight_allows_static_jpeg(): void
    {
        $modx = io_create_test_modx();
        $source = $this->filesystemSource();
        $path = $this->tempDir . '/static.jpg';
        file_put_contents($path, "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xFF\xD9");

        $this->assertFalse(imageoptimizer_is_animated_image($path, 'image/jpeg'));
        $this->assertNull(imageoptimizer_preflight($modx, $source, $path, 'image/jpeg'));
    }

    public function test_preflight_skips_heic_without_decoder(): void
    {
        if (imageoptimizer_can_decode_heic(io_create_test_modx())) {
            $this->markTestSkipped('HEIC decoder available in this environment');
        }

        $modx = io_create_test_modx();
        $source = $this->filesystemSource();
        $path = $this->tempDir . '/photo.heic';
        file_put_contents($path, 'heic-bytes');

        $this->assertSame(
            SkipReason::HeicNoDecoder,
            imageoptimizer_preflight($modx, $source, $path, 'image/heic')
        );
    }

    private function filesystemSource(): modMediaSource
    {
        $source = new modMediaSource();
        $source->set('class_key', 'sources.modFileMediaSource');

        return $source;
    }
}
