<?php

namespace Nevadskiy\Geonames\Parsers;

use Generator;
use Illuminate\Console\OutputStyle;
use Nevadskiy\Geonames\Support\FileReader\FileReader;
use Symfony\Component\Console\Helper\ProgressBar;

class ProgressParser implements Parser
{
    /**
     * The decorated parser instance.
     *
     * @var Parser
     */
    protected $parser;

    /**
     * The progress bar instance.
     *
     * @var ProgressBar
     */
    protected $progress;

    /**
     * The output style instance.
     *
     * @var OutputStyle
     */
    protected $output;

    /**
     * The progress step.
     *
     * @var int
     */
    protected $step;

    /**
     * Make a new progress parser instance.
     */
    public function __construct(Parser $parser, OutputStyle $output, int $step = 1000)
    {
        $this->parser = $parser;
        $this->output = $output;
        $this->step = $step;
    }

    /**
     * {@inheritdoc}
     */
    public function each(string $path): Generator
    {
        $this->startProgress($path);

        $this->progress->advance();

        foreach ($this->parser->each($path) as $i => $line) {
            if ($i % $this->step === 0) {
                $this->progress->advance($this->step);
            }

            yield $line;
        }

        $this->finishProgress();
    }

    /**
     * {@inheritdoc}
     */
    public function getFileReader(): FileReader
    {
        return $this->parser->getFileReader();
    }

    /**
     * Start the progress bar.
     */
    protected function startProgress(string $path): void
    {
        $steps = $this->getFileReader()->getLinesCount($path);

        $this->progress = $this->output->createProgressBar($steps);

        if ($steps) {
            $this->progress->setFormat(
                "<options=bold;fg=green>Processing:</> {$path}\n".
                "%bar% %percent%%\n".
                "<fg=blue>Remaining Time:</> %remaining%\n"
            );
        }
    }

    /**
     * Finish the progress bar.
     */
    protected function finishProgress(): void
    {
        $this->progress->finish();
        $this->output->newLine();
    }

    /**
     * {@inheritdoc}
     */
    public function all(string $path): array
    {
        return iterator_to_array($this->each($path));
    }

    /**
     * {@inheritdoc}
     */
    public function setFields(array $fields): Parser
    {
        return $this->parser->setFields($fields);
    }
}
