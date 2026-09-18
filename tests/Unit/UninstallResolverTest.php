<?php

declare(strict_types=1);

/**
 * Guards against regressing the uninstall hang:
 * file resolvers may delete core/components/imageoptimizer before this script runs.
 */
final class UninstallResolverTest extends PHPUnit\Framework\TestCase
{
    public function test_resolver_does_not_unconditionally_require_component_paths(): void
    {
        $path = dirname(__DIR__, 2) . '/_build/resolvers/resolver_uninstall.php';
        $this->assertFileExists($path);

        $src = (string) file_get_contents($path);

        $this->assertStringNotContainsString(
            "require_once MODX_CORE_PATH . 'components/imageoptimizer/include/paths.php'",
            $src,
            'Uninstall must not hard-require paths.php (files may already be gone)'
        );

        $this->assertStringNotContainsString(
            'imageoptimizer_core_path($modx)',
            $src,
            'Uninstall must not call imageoptimizer_core_path before helpers are loaded'
        );

        // Optional helpers load must be gated.
        $this->assertMatchesRegularExpression(
            '/is_file\s*\(\s*\$helpersPath\s*\)/',
            $src
        );

        $this->assertStringContainsString('DROP TABLE IF EXISTS', $src);
        $this->assertStringContainsString('return true;', $src);
    }

    public function test_resolver_wraps_optional_helpers_in_try_catch(): void
    {
        $path = dirname(__DIR__, 2) . '/_build/resolvers/resolver_uninstall.php';
        $src = (string) file_get_contents($path);

        $this->assertStringContainsString('catch (Throwable', $src);
        $this->assertStringContainsString('imageoptimizer_cleanup_on_uninstall', $src);
    }
}
