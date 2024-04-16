<?php

declare(strict_types=1);

namespace Wentaophp\Proxy\Aop\Annotation;

interface AnnotationInterface
{
    /**
     * Collect the annotation metadata to a container that you want.
     */
    public function collectClass(string $className): void;

    /**
     * Collect the annotation metadata to a container that you want.
     */
    public function collectMethod(string $className, ?string $target): void;

    /**
     * Collect the annotation metadata to a container that you want.
     */
    public function collectProperty(string $className, ?string $target): void;
}
