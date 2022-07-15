<?php

namespace Nevadskiy\Geonames\Reader;

class DeletesReader implements Reader
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
    private $headers = [
        'geonameid',
        'name',
        'comment'
    ];

    /**
     * Make a new reader instance.
     */
    public function __construct(Reader $reader)
    {
        $this->reader = new HeadersReader($reader, $this->headers);
    }

    /**
     * @inheritdoc
     */
    public function getRecords(string $path): iterable
    {
        return $this->reader->getRecords($path);
    }
}
