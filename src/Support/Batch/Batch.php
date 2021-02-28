<?php

namespace Nevadskiy\Geonames\Support\Batch;

class Batch
{
    /**
     * The batch handler.
     *
     * @var callable
     */
    private $handler;

    /**
     * The batch buffer items.
     *
     * @var array
     */
    private $buffer = [];

    /**
     * The current size of the buffer.
     *
     * @var int
     */
    private $currentSize = 0;

    /**
     * The max size of the buffer.
     *
     * @var int
     */
    private $maxSize;

    /**
     * Make a new batch instance with the given handler and size.
     */
    public function __construct(callable $handler, int $maxSize = 100)
    {
        $this->handler = $handler;
        $this->maxSize = $maxSize;
    }

    /**
     * Push the given item to the batch buffer.
     *
     * @param mixed $item
     * @param null $key
     */
    public function push($item, $key = null): void
    {
        $this->add($item, $key);

        if ($this->currentSize >= $this->maxSize) {
            $this->commit();
        }
    }

    /**
     * Add an item to the batch buffer.
     *
     * @param mixed $item
     * @param null $key
     */
    private function add($item, $key = null): void
    {
        if ($key) {
            $this->buffer[$key] = $item;
        } else {
            $this->buffer[] = $item;
        }

        $this->currentSize++;
    }

    /**
     * Reset the batch buffer.
     */
    private function reset(): void
    {
        $this->buffer = [];
        $this->currentSize = 0;
    }

    /**
     * Commit the batch using the attached handler.
     */
    public function commit(): void
    {
        call_user_func($this->handler, $this->buffer);
        $this->reset();
    }

    /**
     * Destroy the batch instance.
     */
    public function __destruct()
    {
        $this->commit();
    }
}
