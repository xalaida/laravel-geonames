<?php

namespace Nevadskiy\Geonames\Reader;

use Illuminate\Console\OutputStyle;

class ConsoleProgressReader implements Reader
{
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
     * Make a new progress parser instance.
     */
    public function __construct(Reader $reader, OutputStyle $output)
    {
        $this->reader = $reader;
        $this->output = $output;
    }

    /**
     * @inheritdoc
     */
    public function getRecords(string $path): iterable
    {
        $progress = $this->output->createProgressBar($this->getFileSize($path));

        $progress->start();

        foreach ($this->reader->getRecords($path) as $record) {
            $progress->advance($this->getRecordSize($record));

            yield $record;
        }

        $progress->finish();
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
        // TODO: consider cleaning file stats

        return filesize($path);
    }
}
