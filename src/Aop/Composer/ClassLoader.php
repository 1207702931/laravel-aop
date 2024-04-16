<?php

namespace Wentaophp\Proxy\Aop\Composer;

use RuntimeException;

class ClassLoader
{
    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function findClassLoader(): \Composer\Autoload\ClassLoader
    {
        $loaders = spl_autoload_functions();

        foreach ($loaders as $loader) {
            if (is_array($loader) && $loader[0] instanceof \Composer\Autoload\ClassLoader) {
                return $loader[0];
            }
        }

        throw new RuntimeException('Composer loader not found.');
    }
}
