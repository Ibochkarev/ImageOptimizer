<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @covers \imageoptimizer_settings_export
 */
final class SettingsTest extends TestCase
{
    public function testBooleanSettingsAreExportedAsBools(): void
    {
        $modx = io_create_test_modx([
            'enabled' => false,
            'convert_on_upload' => false,
            'html_cache' => true,
            'inject_frontend' => true,
            'quality' => 80,
        ]);

        $export = imageoptimizer_settings_export($modx);

        foreach (imageoptimizer_settings_bool_keys() as $key) {
            $this->assertIsBool($export[$key], "Setting '{$key}' must be a bool");
        }

        $this->assertFalse($export['enabled']);
        $this->assertFalse($export['convert_on_upload']);
        $this->assertTrue($export['html_cache']);
        $this->assertSame(80, $export['quality']);
    }

    public function testMissingBooleanSettingDefaultsToFalse(): void
    {
        $modx = io_create_test_modx();

        $export = imageoptimizer_settings_export($modx);

        $this->assertFalse($export['convert_on_upload']);
    }
}
