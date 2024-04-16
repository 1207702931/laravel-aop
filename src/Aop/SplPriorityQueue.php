<?php

declare(strict_types=1);


namespace Wentaophp\Proxy\Aop;

use const PHP_INT_MAX;

/**
 * SplPriorityQueue 的可序列化版本。
 *
 * Also, provides predictable heap order for datums added with the same priority
 * (i.e., they will be emitted in the same order they are enqueued).
 *
 * @template TValue
 * @template TPriority
 * @extends \SplPriorityQueue<TPriority, TValue>
 */
class SplPriorityQueue extends \SplPriorityQueue
{
    /**
     * Seed used to ensure queue order for items of the same priority.
     */
    protected int $serial = PHP_INT_MAX;

    /**
     * 插入具有给定优先级的值。
     *  利用 {@var $serial} 确保优先级相等的值为
     * 以插入顺序相同的顺序发出。
     *
     * @param TValue $value
     * @param TPriority $priority
     */
    public function insert(mixed $value, mixed $priority): true
    {
        return parent::insert($value, [$priority, $this->serial--]);
    }

    /**
     * Serialize to an array.
     *
     * Array will be priority => data pairs
     *
     * @return list<TValue>
     */
    public function toArray(): array
    {
        $array = [];
        foreach (clone $this as $item) {
            $array[] = $item;
        }
        return $array;
    }
}
