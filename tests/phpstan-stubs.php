<?php

/**
 * PHPStan scan symbols for MODX/xPDO used by ImageOptimizer.
 */

declare(strict_types=1);

class modX
{
    public const LOG_LEVEL_ERROR = 1;
    public const LOG_LEVEL_WARN = 2;
    public const LOG_LEVEL_INFO = 3;

    public mixed $event = null;
    public mixed $context = null;
    public mixed $user = null;
    public mixed $resource = null;
    public modLexicon $lexicon;

    public function __construct()
    {
        $this->lexicon = new modLexicon();
    }

    public function getOption(string $key, $options = null, $default = null, bool $skipEvents = false): mixed
    {
        return $default;
    }

    public function setOption(string $key, mixed $value, array $options = []): void
    {
    }

    public function getTableName(string $className): string
    {
        return '';
    }

    public function query(string $sql): mixed
    {
        return null;
    }

    /** @return array<int, xPDOObject> */
    public function getCollection(string $className, $criteria = null, bool $cacheFlag = false): array
    {
        return [];
    }

    public function getObject(string $className, $criteria, bool $cacheFlag = false): ?xPDOObject
    {
        return null;
    }

    public function newObject(string $className): ?xPDOObject
    {
        return null;
    }

    public function exec(string $sql): mixed
    {
        return false;
    }

    public function getCount(string $className, $criteria = null, bool $cacheFlag = false): int
    {
        return 0;
    }

    public function log(int $level, string $msg, array $options = []): void
    {
    }

    public function getCacheManager(): modCacheManager
    {
        return new modCacheManager();
    }

    public function getService(string $name, string $class = '', array $options = []): mixed
    {
        return null;
    }

    /** @return array<string, mixed> */
    public function getVersionData(): array
    {
        return [];
    }

    public function hasPermission(string $permission): bool
    {
        return false;
    }

    public function initialize(string $contextKey = 'web'): bool
    {
        return true;
    }

    public function getRequest(): modRequest
    {
        return new modRequest();
    }

    public function addPackage(string $name, string $path = '', string $namespace = ''): bool
    {
        return true;
    }

    public function beginTransaction(): bool
    {
        return true;
    }

    public function commit(): bool
    {
        return true;
    }

    public function prepare(string $sql): ?PDOStatement
    {
        return null;
    }

    public function quote(string $string, int $type = PDO::PARAM_STR): string
    {
        return "'" . str_replace("'", "''", $string) . "'";
    }

    public function newQuery(string $className): xPDOQuery
    {
        return new xPDOQuery();
    }
}

class modLexicon
{
    public function load(string $topic, bool $merge = true): void
    {
    }

    public function fetch(string $namespace = '', bool $includeFullKey = false): array
    {
        return [];
    }
}

class modRequest
{
    /** @return array<string, mixed> */
    public function getParameters(array $keys = []): array
    {
        return [];
    }
}

class modCacheManager
{
    public function clear(array $targets = []): void
    {
    }
}

class xPDOQuery
{
    public function where($conditions): self
    {
        return $this;
    }

    public function sortby(string $column, string $direction = 'ASC'): self
    {
        return $this;
    }

    public function limit(int $limit, int $offset = 0): self
    {
        return $this;
    }
}

class xPDOObject
{
    public function get(string $key): mixed
    {
        return null;
    }

    public function set(string $key, mixed $value): void
    {
    }

    public function save(bool $cacheFlag = false): bool
    {
        return true;
    }

    /** @param array<int, string> $ancestors */
    public function remove(array $ancestors = []): bool
    {
        return true;
    }

    public function initialize(): bool
    {
        return true;
    }

    /** @param array<string, mixed> $data */
    public function fromArray(array $data, string $prefix = '', bool $ignoreEmpty = false): self
    {
        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(string $prefix = '', bool $rawValues = false): array
    {
        return [];
    }
}

class ioQueue extends xPDOObject
{
}

class modMediaSource extends xPDOObject
{
    public modX $xpdo;

    public function getBasePath(): string
    {
        return '';
    }

    public function getBaseUrl(): string
    {
        return '';
    }

    public function checkPolicy(string|array $criteria, ?array $targets = null, ?modUser $user = null): bool
    {
        return true;
    }

    public function hasPermission(string $key): bool
    {
        return true;
    }
}

class modSystemSetting extends xPDOObject
{
}

class modResource extends xPDOObject
{
}

class modContext extends xPDOObject
{
    public function get(string $key): mixed
    {
        return 'mgr';
    }
}

class modUser extends xPDOObject
{
    public function isAuthenticated(string $contextKey = 'web'): bool
    {
        return true;
    }

    public function getUserToken(string $contextKey = 'web'): string
    {
        return 'token';
    }
}

class modProcessorResponse
{
    public function isError(): bool
    {
        return false;
    }

    public function getMessage(): string
    {
        return '';
    }
}

class modSystemSettingUpdateProcessor
{
    public static function run(array $data): modProcessorResponse
    {
        return new modProcessorResponse();
    }
}
