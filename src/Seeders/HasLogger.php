<?php

namespace Nevadskiy\Geonames\Seeders;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

trait HasLogger
{
    /**
     * The logger instance.
     *
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * Set the logger instance.
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Get the logger instance.
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger ?: new NullLogger();
    }
}
