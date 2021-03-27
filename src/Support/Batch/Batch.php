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
     * The batch buffer items.
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
    protected function add($item, $key = null): void
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
