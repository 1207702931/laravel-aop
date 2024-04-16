<?php

declare(strict_types=1);


namespace Wentaophp\Proxy\Aop\Collector;

interface MetadataCollectorInterface
{
    /**
     * Retrieve the metadata via key.
     * @param mixed|null $default
     */
    public static function get(string $key, mixed $default = null);

    /**
     * Set the metadata to holder.
     * @param string $key
     * @param mixed $value
     */
    public static function set(string $key, mixed $value): void;

    public static function clear(?string $key = null): void;

    /**
     * Serialize the all metadata to a string.
     */
    public static function serialize(): string;

    /**
     * Deserialize the serialized metadata and set the metadata to holder.
     */
    public static function deserialize(string $metadata): bool;

    /**
     * Return all metadata array.
     */
    public static function list(): array;
}
