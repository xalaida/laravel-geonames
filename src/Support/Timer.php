<?php

namespace Nevadskiy\Geonames\Support;

class Timer
{
    /**
     * Static constructor.
     *
     * @return static
     */
    public static function new()
    {
        return new static;
    }

    /**
     * Measure execution time in seconds.
     *
     * @param callable $callback
     * @return float|string
     */
    public function measure(callable $callback)
    {
        $startTime = microtime(true);

        $callback();

        return microtime(true) - $startTime;
    }

    /**
     * Measure function alias.
     *
     * @param callable $callback
     * @return float|string
     */
    public function measureInSeconds(callable $callback)
    {
        return $this->measure($callback);
    }
}
