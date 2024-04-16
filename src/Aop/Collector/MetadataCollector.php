<?php

declare(strict_types=1);


namespace Wentaophp\Proxy\Aop\Collector;

use Illuminate\Support\Arr;

abstract class MetadataCollector implements MetadataCollectorInterface
{
    /**
     * 子类必须覆盖此属性。
     */
    protected static array $container = [];

    /**
     * 通过 key 检索元数据。
     * @param mixed|null $default
     */
    public static function get(string $key, mixed $default = null)
    {
        return Arr::get(static::$container, $key) ?? $default;
    }

    /**
     * 将元数据设置为 holder。
     * @param mixed $value
     */
    public static function set(string $key, mixed $value): void
    {
        Arr::set(static::$container, $key, $value);
    }

    /**
     * Determine if the metadata exist.
     * If exist will return true, otherwise return false.
     */
    public static function has(string $key): bool
    {
        return Arr::has(static::$container, $key);
    }

    public static function clear(?string $key = null): void
    {
        if ($key) {
            Arr::forget(static::$container, [$key]);
        } else {
            static::$container = [];
        }
    }

    /**
     * Serialize the all metadata to a string.
     */
    public static function serialize(): string
    {
        return serialize(static::$container);
    }

    /**
     * Deserialize the serialized metadata and set the metadata to holder.
     */
    public static function deserialize(string $metadata): bool
    {
        static::$container = unserialize($metadata);
        return true;
    }

    public static function list(): array
    {
        return static::$container;
    }
}
