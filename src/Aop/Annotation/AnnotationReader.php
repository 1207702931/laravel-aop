<?php

declare(strict_types=1);


namespace Wentaophp\Proxy\Aop\Annotation;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Reflector;

/**
 * A reader for docblock annotations.
 */
class AnnotationReader
{
    public function __construct(protected array $ignoreAnnotations = [])
    {
    }

    /**
     * @param ReflectionClass $class
     * @return array
     * @throws \Exception
     */
    public function getClassAnnotations(ReflectionClass $class): array
    {
        return $this->getAttributes($class);
    }

    /**
     * @param ReflectionClass $class
     * @param $annotationName
     * @return null|\ReflectionAttribute
     * @throws \Exception
     */
    public function getClassAnnotation(ReflectionClass $class, $annotationName): ?\ReflectionAttribute
    {
        $annotations = $this->getClassAnnotations($class);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * @param ReflectionProperty $property
     * @return array
     * @throws \Exception
     */
    public function getPropertyAnnotations(ReflectionProperty $property): array
    {
        return $this->getAttributes($property);
    }

    /**
     * @param ReflectionProperty $property
     * @param $annotationName
     * @return null|\ReflectionAttribute
     * @throws \Exception
     */
    public function getPropertyAnnotation(ReflectionProperty $property, $annotationName): ?\ReflectionAttribute
    {
        $annotations = $this->getPropertyAnnotations($property);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * @param ReflectionMethod $method
     * @return array
     * @throws \Exception
     */
    public function getMethodAnnotations(ReflectionMethod $method): array
    {
        return $this->getAttributes($method);
    }

    /**
     * @param ReflectionMethod $method
     * @param $annotationName
     * @return null|\ReflectionAttribute
     * @throws \Exception
     */
    public function getMethodAnnotation(ReflectionMethod $method, $annotationName): ?\ReflectionAttribute
    {
        $annotations = $this->getMethodAnnotations($method);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * @throws \Exception
     */
    public function getAttributes(Reflector $reflection): array
    {
        $result = [];
        if (! method_exists($reflection, 'getAttributes')) {
            return $result;
        }

        $attributes = $reflection->getAttributes();

        foreach ($attributes as $attribute) {
            if (in_array($attribute->getName(), $this->ignoreAnnotations, true)) {
                continue;
            }
            if (! class_exists($attribute->getName())) {
                $className = $methodName = $propertyName = '';
                if ($reflection instanceof ReflectionClass) {
                    $className = $reflection->getName();
                } elseif ($reflection instanceof ReflectionMethod) {
                    $className = $reflection->getDeclaringClass()->getName();
                    $methodName = $reflection->getName();
                } elseif ($reflection instanceof ReflectionProperty) {
                    $className = $reflection->getDeclaringClass()->getName();
                    $propertyName = $reflection->getName();
                }
                $message = sprintf(
                    "No attribute class found for '%s' in %s",
                    $attribute->getName(),
                    $className
                );
                if ($methodName) {
                    $message .= sprintf('->%s() method', $methodName);
                }
                if ($propertyName) {
                    $message .= sprintf('::$%s property', $propertyName);
                }
                throw new \Exception($message);
            }
            $result[] = $attribute->newInstance();
        }
        return $result;
    }
}
