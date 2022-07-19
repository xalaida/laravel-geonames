<?php

namespace Nevadskiy\Geonames\Reader;

class HeadersReader implements Reader
{
    /**
     * The reader instance.
     *
     * @var Reader
     */
    protected $reader;

    /**
     * The record headers.
     *
     * @var array
     */
    protected $headers;

    /**
     * Make a new reader instance.
     */
    public function __construct(Reader $reader, array $headers = [])
    {
        $this->reader = $reader;
        $this->headers = $headers;
    }

    /**
     * @inheritdoc
     */
    public function getRecords(string $path): iterable
    {
        foreach ($this->reader->getRecords($path) as $record) {
            yield $this->map($record);
        }
    }

    /**
     * Map headers to the record.
     */
    protected function map(array $record): array
    {
        $values = [];

        foreach ($this->headers as $index => $header) {
            $values[$header] = $this->getRecordValue($record, $index);
        }

        return $values;
    }

    /**
     * Get the record value at the given index.
     */
    protected function getRecordValue(array $record, int $index)
    {
        if (! isset($record[$index])) {
            return null;
        }

        $value = $record[$index];

        if ($value === '') {
            return null;
        }

        return $value;
    }
}
