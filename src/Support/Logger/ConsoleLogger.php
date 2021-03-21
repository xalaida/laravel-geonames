<?php

namespace Nevadskiy\Geonames\Support\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLogger extends AbstractLogger
{
    /**
     * The output instance.
     *
     * @var OutputInterface
     */
    private $output;

    /**
     * The level colors map.
     *
     * @var string[]
     */
    protected $colors =  [
        LogLevel::WARNING => 'yellow',
        LogLevel::NOTICE => 'cyan',
        LogLevel::INFO => 'green',
    ];

    /**
     * Make a new console logger instance.
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []): void
    {
        $this->output->writeln($this->format($level, $message));
    }

    /**
     * Format the message by the level.
     *
     * @param $level
     * @param string $message
     * @return string
     */
    protected function format(string $level, string $message): string
    {
        return sprintf($this->style($level), $message);
    }

    /**
     * Get a style for the level.
     *
     * @param string $level
     * @return string
     */
    public function style(string $level): string
    {
        $color = $this->color($level);

        if (! $color) {
            return '%s';
        }

        return "<options=bold,reverse;fg={$color}> ". strtoupper($level) . " </> %s";
    }

    /**
     * Get a color by the given level.
     *
     * @param $level
     * @return string|null
     */
    protected function color($level): ?string
    {
        return $this->colors[$level] ?? null;
    }
}
