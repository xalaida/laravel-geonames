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
     * Make a new progress parser instance.
     */
    public function __construct(Parser $parser, OutputStyle $output)
    {
        $this->parser = $parser;
        $this->output = $output;
    }

    /**
     * @inheritDoc
     */
    public function each(string $path): Generator
    {
        $this->startProgress($path);

        $this->progress->advance();

        yield from $this->parser->each($path);

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
    }

    /**
     * Finish the progress bar.
     */
    protected function finishProgress(): void
    {
        $this->progress->finish();
        $this->output->newLine();
    }

    public function all(string $path): array
    {
        // TODO: check how this works with iterator_to_array and progress at same time.
    }

    /**
     * @inheritDoc
     */
    public function setFields(array $fields): Parser
    {
        return $this->parser->setFields($fields);
    }
}
