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
    private $output;

    /**
     * The progress step.
     *
     * @var int
     */
    private $step;

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
     * @inheritDoc
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
     * @inheritDoc
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
        $this->progress = $this->output->createProgressBar(
            $this->getFileReader()->getLinesCount($path)
        );

        $this->progress->setFormat('very_verbose');
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
     * @inheritDoc
     */
    public function all(string $path): array
    {
        return iterator_to_array($this->each($path));
    }

    /**
     * @inheritDoc
     */
    public function setFields(array $fields): Parser
    {
        return $this->parser->setFields($fields);
    }
}
