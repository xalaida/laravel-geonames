<?php

namespace Nevadskiy\Geonames\Reader;

class HeadersReader implements Reader
{
    /**
     * The reader instance.
     *
     * @var Reader
     */
    private $reader;

    /**
     * The record headers.
     *
     * @var array
     */
    private $headers;

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

        foreach ($record as $key => $value) {
            $values[$this->headers[$key] ?? $key] = $value !== '' ? $value : null;
        }

        return $values;
    }
}
