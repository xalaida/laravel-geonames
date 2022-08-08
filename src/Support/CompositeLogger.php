<?php

namespace Nevadskiy\Geonames\Support;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class CompositeLogger extends AbstractLogger
{
    /**
     * The logger list.
     *
     * @var LoggerInterface
     */
    protected $loggers;

    /**
     * Make a new logger instance.
     */
    public function __construct(LoggerInterface ...$loggers)
    {
        $this->loggers = $loggers;
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = []): void
    {
        foreach ($this->loggers as $logger) {
            $logger->log($level, $message, $context);
        }
    }
}
