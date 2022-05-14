<?php

namespace Nevadskiy\Geonames\Support\Batch;

class Batch
{
    /**
     * The batch handler.
     *
     * @var callable
     */
    protected $handler;

    /**
     * The batch buffer.
     *
     * @var array
     */
    protected $buffer = [];

    /**
     * The current size of the buffer.
     *
     * @var int
     */
    protected $currentSize = 0;

    /**
     * The max size of the buffer.
     *
     * @var int
     */
    protected $maxSize;

    /**
     * Make a new batch instance with the given handler and size.
     */
    public function __construct(callable $handler, int $maxSize = 1000)
    {
        $this->handler = $handler;
        $this->maxSize = $maxSize;
    }

    /**
     * Push the given item to the batch buffer.
     *
     * @param mixed $item
     * @param mixed $key
     */
    public function push($item, $key = null): void
    {
        $this->pushItem($item, $key);

        if ($this->currentSize >= $this->maxSize) {
            $this->commit();
        }
    }

    /**
     * Push an item to the batch buffer.
     *
     * @param mixed $item
     * @param mixed $key
     */
    protected function pushItem($item, $key = null): void
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
    protected function reset(): void
    {
        $this->buffer = [];
        $this->currentSize = 0;
    }

    /**
     * Commit the batch using the attached handler.
     */
    public function commit(): void
    {
        if ($this->currentSize > 0) {
            call_user_func($this->handler, $this->buffer);
        }

        $this->reset();
    }
}
