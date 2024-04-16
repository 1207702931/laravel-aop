<?php

declare(strict_types=1);


namespace Wentaophp\Proxy\Aop;

use Illuminate\Contracts\Support\Arrayable;
use ReflectionProperty;
use Wentaophp\Proxy\Aop\Annotation\AnnotationInterface;
use Wentaophp\Proxy\Aop\Collector\AnnotationCollector;

abstract class AbstractAnnotation implements AnnotationInterface, Arrayable
{
    public function toArray(): array
    {
        $properties = ReflectionManager::reflectClass(static::class)->getProperties(ReflectionProperty::IS_PUBLIC);
        $result = [];
        foreach ($properties as $property) {
            $result[$property->getName()] = $property->getValue($this);
        }
        return $result;
    }

    public function collectClass(string $className): void
    {
        AnnotationCollector::collectClass($className, static::class, $this);
    }

    public function collectMethod(string $className, ?string $target): void
    {
        AnnotationCollector::collectMethod($className, $target, static::class, $this);
    }

    public function collectProperty(string $className, ?string $target): void
    {
        AnnotationCollector::collectProperty($className, $target, static::class, $this);
    }
}
