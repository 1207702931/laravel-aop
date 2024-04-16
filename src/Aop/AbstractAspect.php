<?php

declare(strict_types=1);

namespace Wentaophp\Proxy\Aop;


abstract class AbstractAspect implements AroundInterface
{
    /**
     * The classes that you want to weave.
     */
    public array $classes = [];

    /**
     * The annotations that you want to weave.
     */
    public array $annotations = [];

    public ?int $priority = null;
}
