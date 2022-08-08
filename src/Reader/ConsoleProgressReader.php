<?php

namespace Nevadskiy\Geonames\Reader;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressBar;
use function strlen;

class ConsoleProgressReader implements Reader
{
    /**
     * The default progress format name of the reader.
     */
    protected const PROGRESS_FORMAT = 'reader';

    /**
     * The progress format name of the reader when maximum steps are not available.
     */
    protected const PROGRESS_FORMAT_NOMAX = 'reader_nomax';

    /**
     * The reader instance.
     *
     * @var GeonamesReader
     */
    protected $reader;

    /**
     * The output style instance.
     *
     * @var OutputStyle
     */
    protected $output;

    /**
     * A format of the progress bar.
     *
     * @var string|null
     */
    protected $format = self::PROGRESS_FORMAT;

    /**
     * Indicates if a new line should be printed when progress bar finishes.
     *
     * @var string
     */
    protected $printsNewLine = true;

    /**
     * Make a new progress parser instance.
     */
    public function __construct(Reader $reader, OutputStyle $output)
    {
        $this->reader = $reader;
        $this->output = $output;

        $this->setFormatDefinition();
    }

    /**
     * Set up a human-readable progress format.
     */
    protected function setFormatDefinition(): void
    {
        ProgressBar::setFormatDefinition(
            self::PROGRESS_FORMAT,
            ' %size_processed%/%size_total% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%'
        );

        ProgressBar::setFormatDefinition(
            self::PROGRESS_FORMAT_NOMAX,
            ' %size_processed% [%bar%] %percent:3s%% %elapsed:6s%'
        );

        ProgressBar::setPlaceholderFormatterDefinition('size_processed', function (ProgressBar $bar) {
            return Helper::formatMemory($bar->getProgress());
        });

        ProgressBar::setPlaceholderFormatterDefinition('size_total', function (ProgressBar $bar) {
            return Helper::formatMemory($bar->getMaxSteps());
        });
    }

    /**
     * Specify a format of the progress bar.
     */
    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    /**
     * @inheritdoc
     */
    public function getRecords(string $path): iterable
    {
        $progress = $this->output->createProgressBar($this->getFileSize($path));

        $progress->setFormat($this->format);

        $progress->setMessage($path, 'path');

        $progress->start();

        foreach ($this->reader->getRecords($path) as $record) {
            yield $record;

            $progress->advance($this->getRecordSize($record));
        }

        $progress->finish();

        if ($this->printsNewLine) {
            $this->output->newLine();
        }
    }

    /**
     * Get the record size in bytes.
     */
    protected function getRecordSize(array $record): int
    {
        return strlen(implode(' ', $record));
    }

    /**
     * Get a size of the file in bytes.
     */
    protected function getFileSize(string $path): int
    {
        return filesize($path);
    }
}
