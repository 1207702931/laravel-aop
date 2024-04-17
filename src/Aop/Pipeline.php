<?php

declare(strict_types=1);


namespace Wentaophp\Proxy\Aop;

use Closure;
use Psr\Container\ContainerInterface;

/**
 * This file mostly code come from illuminate/pipe,
 * thanks Laravel Team provide such a useful class.
 */
class Pipeline
{
    /**
     * The object being passed through the pipeline.
     */
    protected mixed $passable = null;

    /**
     * The array of class pipes.
     */
    protected array $pipes = [];

    /**
     * The method to call on each pipe.
     */
    protected string $method = 'handle';

    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * Set the object being sent through the pipeline.
     */
    public function send(mixed $passable): self
    {
        $this->passable = $passable;

        return $this;
    }

    /**
     * Set the array of pipes.
     *
     * @param array|mixed $pipes
     */
    public function through($pipes): self
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();
        return $this;
    }

    /**
     * Set the method to call on the pipes.
     */
    public function via(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Run the pipeline with a final destination callback.
     */
    public function then(Closure $destination)
    {
        $pipeline = array_reduce(array_reverse($this->pipes), $this->carry(), $this->prepareDestination($destination));
        return $pipeline($this->passable);
    }

    /**
     * Run the pipeline and return the result.
     */
    public function thenReturn()
    {
        return $this->then(fn($passable) => $passable);
    }

    /**
     * 第一次的回调。
     */
    protected function prepareDestination(Closure $destination): Closure
    {
        return static function ($passable) use ($destination) {
            return $destination($passable);
        };
    }

    /**
     * 获取表示应用程序洋葱切片的 Closure。
     * array_reduce($carry, $item) 中回调函数参数的顺序是：
     * $carry 为上一次迭代的返回，第一次时 为 null
     * $item 为当前迭代的值
     */
    protected function carry(): Closure
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                if (is_string($pipe) && class_exists($pipe)) {
                    $pipe = $this->container->get($pipe);
                }
                if (!$passable instanceof ProceedingJoinPoint) {
                    throw new \Exception('$passable must is a ProceedingJoinPoint object.');
                }
                $passable->pipe = $stack;
                return method_exists($pipe, $this->method) ? $pipe->{$this->method}($passable) : $pipe($passable);
            };
        };
    }

    /**
     * Parse full pipe string to get name and parameters.
     *
     * @param string $pipe
     * @return array
     */
    protected function parsePipeString($pipe)
    {
        [$name, $parameters] = array_pad(explode(':', $pipe, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }

    /**
     * Handle the value returned from each pipe before passing it to the next.
     *
     * @param mixed $carry
     * @return mixed
     */
    protected function handleCarry($carry)
    {
        return $carry;
    }
}
