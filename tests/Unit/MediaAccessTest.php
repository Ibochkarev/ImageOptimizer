<?php

declare(strict_types=1);

final class MediaAccessTest extends PHPUnit\Framework\TestCase
{
    public function test_allows_access_when_policy_and_permission_ok(): void
    {
        $modx = io_create_test_modx();
        $source = new modMediaSource();

        $this->assertTrue(imageoptimizer_user_can_access_media_source($modx, $source, 'read'));
        $this->assertTrue(imageoptimizer_user_can_access_media_source($modx, $source, 'write'));
        $this->assertTrue(imageoptimizer_user_can_access_media_source($modx, $source, 'remove'));
    }

    public function test_denies_when_policy_blocks(): void
    {
        $modx = io_create_test_modx();
        $source = new modMediaSource();
        $source->policyAllow = false;

        $this->assertFalse(imageoptimizer_user_can_access_media_source($modx, $source, 'read'));
    }

    public function test_denies_when_permission_missing(): void
    {
        $modx = io_create_test_modx();
        $source = new modMediaSource();
        $source->permissionAllow = false;

        $this->assertFalse(imageoptimizer_user_can_access_media_source($modx, $source, 'write'));
    }

    public function test_denies_when_source_fails_to_initialize(): void
    {
        $modx = io_create_test_modx();
        $source = new modMediaSource();
        $source->initOk = false;

        $this->assertFalse(imageoptimizer_user_can_access_media_source($modx, $source, 'read'));
    }

    public function test_get_media_source_returns_null_for_invalid_id(): void
    {
        $modx = io_create_test_modx();

        $this->assertNull(imageoptimizer_get_media_source($modx, 0));
        $this->assertNull(imageoptimizer_get_media_source($modx, -1));
    }
}
