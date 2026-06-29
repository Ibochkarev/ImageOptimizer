<?php

declare(strict_types=1);

/**
 * Minimal MODX stubs for PHPUnit (not used by PHPStan).
 */
class modX
{
    public modContext $context;

    public ?modResource $resource = null;

    /** @var array<string, mixed> */
    private array $options = [];

    public function __construct(string $contextKey = 'web')
    {
        $this->context = new modContext($contextKey);
    }

    public function setOption(string $key, mixed $value): void
    {
        $this->options[$key] = $value;
    }

    public function getOption(string $key, $options = null, $default = null, bool $skipEvents = false): mixed
    {
        return $this->options[$key] ?? $default;
    }

    public function getObject(string $className, $criteria, bool $cacheFlag = false): ?object
    {
        return null;
    }

    /** @return array<int, object> */
    public function getCollection(string $className, $criteria = null, bool $cacheFlag = false): array
    {
        return [];
    }
}

class modContext
{
    public function __construct(private string $key = 'web')
    {
    }

    public function get(string $key): mixed
    {
        return $key === 'key' ? $this->key : null;
    }
}

class modResource
{
    /** @var array<string, mixed> */
    private array $fields = [];

    public function get(string $key): mixed
    {
        return $this->fields[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->fields[$key] = $value;
    }
}

class modContentType
{
    /** @var array<string, mixed> */
    private array $fields = [];

    public function get(string $key): mixed
    {
        return $this->fields[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->fields[$key] = $value;
    }
}

class modMediaSource
{
    /** @var array<string, mixed> */
    private array $fields = [];

    public bool $initOk = true;

    public bool $policyAllow = true;

    public bool $permissionAllow = true;

    public function get(string $key): mixed
    {
        return $this->fields[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->fields[$key] = $value;
    }

    public function initialize(): bool
    {
        return $this->initOk;
    }

    public function checkPolicy(string|array $criteria, ?array $targets = null, ?object $user = null): bool
    {
        return $this->policyAllow;
    }

    public function hasPermission(string $key): bool
    {
        return $this->permissionAllow;
    }
}

class modUser
{
}

/**
 * @param array<string, mixed> $settings Keys without imageoptimizer_ prefix.
 */
function io_create_test_modx(array $settings = [], string $contextKey = 'web'): modX
{
    $modx = new modX($contextKey);
    $modx->setOption('imageoptimizer_html_cache', '0');
    $modx->setOption('imageoptimizer_inject_frontend', '1');
    foreach ($settings as $key => $value) {
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }
        $modx->setOption('imageoptimizer_' . $key, $value);
    }

    return $modx;
}

function io_img_from_markup(string $markup): DOMElement
{
    $doc = new DOMDocument();
    $doc->loadHTML(
        '<?xml encoding="UTF-8"><html><body>' . $markup . '</body></html>',
        LIBXML_NOERROR | LIBXML_NOWARNING
    );
    $img = $doc->getElementsByTagName('img')->item(0);
    if (!$img instanceof DOMElement) {
        throw new RuntimeException('No img in markup: ' . $markup);
    }

    return $img;
}

function io_fixture_html(string $name): string
{
    $path = dirname(__DIR__) . '/fixtures/inject/' . $name;
    if (!is_readable($path)) {
        throw new RuntimeException('Fixture not found: ' . $path);
    }

    return trim((string) file_get_contents($path));
}

/**
 * modX test double with optional modContentType lookup for inject tests.
 */
final class ModxInjectTestDouble extends modX
{
    public ?modContentType $contentTypeObject = null;

    public function getObject(string $className, $criteria, bool $cacheFlag = false): ?object
    {
        if ($className === 'modContentType' && $this->contentTypeObject !== null) {
            return $this->contentTypeObject;
        }

        return null;
    }
}
