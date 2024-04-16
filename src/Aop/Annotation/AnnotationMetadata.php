<?php

declare(strict_types=1);


namespace Wentaophp\Proxy\Aop\Annotation;

class AnnotationMetadata
{
    public function __construct(public array $class, public array $method)
    {
    }
}
