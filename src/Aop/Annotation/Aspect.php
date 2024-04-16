<?php

declare(strict_types=1);

namespace Wentaophp\Proxy\Aop\Annotation;

use Attribute;
use InvalidArgumentException;
use Wentaophp\Proxy\Aop\AbstractAnnotation;
use Wentaophp\Proxy\Aop\Collector\AspectCollector;

#[Attribute(Attribute::TARGET_CLASS)]
class Aspect extends AbstractAnnotation
{
    public function __construct(public array $classes = [], public array $annotations = [], public ?int $priority = null)
    {
    }

    public function collectClass(string $className): void
    {
        parent::collectClass($className);
        $this->collect($className);
    }

    protected function collect(string $className)
    {
        [$instanceClasses, $instanceAnnotations, $instancePriority] = AspectLoader::load($className);

        // Classes
        $classes = $this->classes;
        $classes = $instanceClasses ? array_merge($classes, $instanceClasses) : $classes;
        // Annotations
        $annotations = $this->annotations;
        $annotations = $instanceAnnotations ? array_merge($annotations, $instanceAnnotations) : $annotations;
        // Priority
        $annotationPriority = $this->priority;
        $propertyPriority = $instancePriority ?: null;
        if (!is_null($annotationPriority) && !is_null($propertyPriority) && $annotationPriority !== $propertyPriority) {
            throw new InvalidArgumentException('Cannot define two difference priority of Aspect.');
        }
        $priority = $annotationPriority ?? $propertyPriority;
        // Save the metadata to AspectCollector
        AspectCollector::setAround($className, $classes, $annotations, $priority);
    }
}
