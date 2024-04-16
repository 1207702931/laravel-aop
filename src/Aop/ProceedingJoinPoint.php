<?php

declare(strict_types=1);


namespace Wentaophp\Proxy\Aop;

use Closure;
use Exception;
use ReflectionFunction;
use ReflectionMethod;
use Wentaophp\Proxy\Aop\Annotation\AnnotationMetadata;
use Wentaophp\Proxy\Aop\Collector\AnnotationCollector;

class ProceedingJoinPoint
{
    public mixed $result;

    public ?Closure $pipe = null;

    public function __construct(
        public Closure $originalMethod,
        public string $className,
        public string $methodName,
        public array $arguments
    ) {
    }

    /**
     * Delegate to the next aspect.
     */
    public function process()
    {
        $closure = $this->pipe;
        if (! $closure instanceof Closure) {
            throw new Exception('The pipe is not instanceof \Closure');
        }

        return $closure($this);
    }

    /**
     * Process the original method, this method should trigger by pipeline.
     */
    public function processOriginalMethod()
    {
        $this->pipe = null;
        $closure = $this->originalMethod;
        $arguments = $this->getArguments();
        return $closure(...$arguments);
    }

    public function getAnnotationMetadata(): AnnotationMetadata
    {
        $metadata = AnnotationCollector::get($this->className);
        return new AnnotationMetadata($metadata['_c'] ?? [], $metadata['_m'][$this->methodName] ?? []);
    }

    public function getArguments(): array
    {
        $result = [];
        foreach ($this->arguments['order'] ?? [] as $order) {
            $result[] = $this->arguments['keys'][$order];
        }

        // Variable arguments are always placed at the end.
        if (isset($this->arguments['variadic'], $order) && $order === $this->arguments['variadic']) {
            $variadic = array_pop($result);
            $result = array_merge($result, $variadic);
        }
        return $result;
    }

    public function getReflectMethod(): ReflectionMethod
    {
        return ReflectionManager::reflectMethod(
            $this->className,
            $this->methodName
        );
    }

    public function getInstance(): ?object
    {
        $ref = new ReflectionFunction($this->originalMethod);

        return $ref->getClosureThis();
    }
}
