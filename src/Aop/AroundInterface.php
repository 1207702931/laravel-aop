<?php

declare(strict_types=1);


namespace Wentaophp\Proxy\Aop;

interface AroundInterface
{
    /**
     * @return mixed return the value from process method of ProceedingJoinPoint, or the value that you handled
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed;
}
