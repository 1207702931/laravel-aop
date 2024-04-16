<?php

declare(strict_types=1);


namespace Wentaophp\Proxy\Aop;

class PropertyHandlerManager
{
    private static array $container = [];

    public static function register(string $annotation, callable $callback)
    {
        static::$container[$annotation][] = $callback;
    }

    public static function has(string $annotation): bool
    {
        return isset(static::$container[$annotation]);
    }

    /**
     * @return null|callable[]
     */
    public static function get(string $annotation): ?array
    {
        return static::$container[$annotation] ?? null;
    }

    public static function all(): array
    {
        return static::$container;
    }

    public static function isEmpty(): bool
    {
        return empty(static::all());
    }
}
