<?php

declare(strict_types=1);


namespace Wentaophp\Proxy\Aop;

class AspectManager
{
    protected static array $container = [];

    public static function get($class, $method)
    {
        return static::$container[$class][$method] ?? [];
    }

    public static function has($class, $method): bool
    {
        return isset(static::$container[$class][$method]);
    }

    public static function set($class, $method, $value): void
    {
        static::$container[$class][$method] = $value;
    }

    public static function insert($class, $method, $value): void
    {
        static::$container[$class][$method][] = $value;
    }

    public static function getContainer(): array
    {
        return self::$container;
    }

    public static function setContainer(array $container): void
    {
        self::$container = $container;
    }
}
