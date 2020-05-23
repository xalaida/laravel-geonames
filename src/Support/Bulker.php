<?php

namespace Nevadskiy\Geonames\Support;

class Bulker
{
    /**
     * The bulk handler function.
     *
     * @var callable
     */
    private $handler;

    /**
     * The bulk data.
     *
     * @var array
     */
    private $bulk = [];

    /**
     * The buffer current size.
     *
     * @var int
     */
    private $currentSize = 0;

    /**
     * The buffer max size.
     *
     * @var int
     */
    private $maxSize;

    /**
     * Bulker constructor.
     *
     * @param callable $handler
     * @param int $maxSize
     */
    public function __construct(callable $handler, int $maxSize = 100)
    {
        $this->handler = $handler;
        $this->maxSize = $maxSize;
    }

    /**
     * Push the given item to the bulk buffer.
     *
     * @param mixed $item
     */
    public function push($item): void
    {
        if ($this->currentSize >= $this->maxSize) {
            $this->commit();
        }

        $this->add($item);
    }

    /**
     * Add an item to the bulk buffer.
     *
     * @param $item
     */
    private function add($item): void
    {
        $this->bulk[] = $item;
        $this->currentSize++;
    }

    /**
     * Reset the bulk buffer.
     */
    private function reset(): void
    {
        $this->bulk = [];
        $this->currentSize = 0;
    }

    /**
     * Commit the bulk data using handler.
     */
    public function commit(): void
    {
        call_user_func($this->handler, $this->bulk);
        $this->reset();
    }
}
