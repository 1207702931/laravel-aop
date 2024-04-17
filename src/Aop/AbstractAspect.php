<?php

declare(strict_types=1);

namespace Wentaophp\Proxy\Aop;


abstract class AbstractAspect implements AroundInterface
{
    /**
     * 要切入的类或 Trait，可以多个，亦可通过 :: 标识到具体的某个方法，通过 * 可以模糊匹配
     */
    public array $classes = [];

    /**
     *  应用到哪些方法或者类注解
     */
    public array $annotations = [];

    /**
     * 切片优先级，值越大越优先，默认为 null，表示不设置优先级 1 > null > -1
     */
    public ?int $priority = null;

    public function getAnnotationInstance(\ReflectionMethod $reflection, string $annotation): mixed
    {
        return $reflection->getAttributes($annotation)[0]->newInstance();
    }
}
