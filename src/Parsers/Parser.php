<?php

namespace Nevadskiy\Geonames\Parsers;

use Generator;
use Nevadskiy\Geonames\Support\FileReader\FileReader;
use Nevadskiy\Geonames\Support\Traits\Events;

// TODO: probably refactor to allow using as a class with API: new Parser(['field1', 'field2', 'field3'])
abstract class Parser
{
    use Events;

    /**
     * The file reader instance.
     *
     * @var FileReader
     */
    protected $fileReader;

    /**
     * Whether the parser should get lines count of the file.
     *
     * @var bool
     */
    protected $shouldGetLinesCount = false;

    /**
     * Amount of line to be parsed to call each event.
     *
     * @var int
     */
    protected $eachLineAfter = 1;

    /**
     * CountryInfoParser constructor.
     *
     * @param FileReader $fileReader
     */
    public function __construct(FileReader $fileReader)
    {
        $this->fileReader = $fileReader;
    }

    /**
     * Get the data fields mapping.
     *
     * @return array|string[]
     */
    abstract protected function fieldsMapping(): array;

    /**
     * Determine whether the parser should skip empty lines.
     */
    protected function shouldSkipEmptyLines(): bool
    {
        return true;
    }

    /**
     * Determine whether the parser should skip lines with leading hashes.
     */
    protected function shouldSkipLeadingHashLines(): bool
    {
        return true;
    }

    /**
     * Determine whether the parser should skip lines that contain headings.
     */
    protected function shouldSkipHeadingLines(): bool
    {
        return true;
    }

    /**
     * Get the line key name.
     */
    protected function getLineKeyName(): string
    {
        return 'geonameid';
    }

    /**
     * Determine whether the parser should get lines count of the file.
     */
    public function shouldGetLinesCount(): bool
    {
        return $this->shouldGetLinesCount;
    }

    /**
     * Enable counting lines before parsing.
     *
     * @return $this
     */
    public function enableCountingLines(): self
    {
        $this->shouldGetLinesCount = true;

        return $this;
    }

    /**
     * Add the given callback to the ready event.
     */
    public function onReady(callable $callback): self
    {
        return $this->onEvent('ready', $callback);
    }

    /**
     * Fire the ready event.
     */
    public function fireReadyEvent(?int $linesCount): void
    {
        $this->fireEvent('ready', [$linesCount]);
    }

    /**
     * Add the given callback when each line after previous amount is parsed.
     */
    public function onEach(callable $callback, int $after = 1): self
    {
        $this->eachLineAfter = $after;

        return $this->onEvent('each', $callback);
    }

    /**
     * Fire the each event with the line index payload.
     */
    public function fireEachEvent(int $lineIndex): void
    {
        if ($lineIndex % $this->eachLineAfter === 0) {
            $this->fireEvent('each');
        }
    }

    /**
     * Add the given callback to the finish event.
     */
    public function onFinish(callable $callback): self
    {
        return $this->onEvent('finish', $callback);
    }

    /**
     * Fire the finish event.
     */
    public function fireFinishEvent(): void
    {
        $this->fireEvent('finish');
    }

    /**
     * Parse the data line by line.
     *
     * @param string $path
     * @return Generator
     */
    public function forEach(string $path): Generator
    {
        $this->fireReadyEvent($this->prepareLinesCount($path));

        foreach ($this->fileReader->forEachLine($path) as $index => $line) {
            $this->fireEachEvent($index);

            if (! $line && $this->shouldSkipEmptyLines()) {
                continue;
            }

            if ($line[0] === '#' && $this->shouldSkipLeadingHashLines()) {
                continue;
            }

            $data = $this->parseLine($line);

            if ($this->isHeadingLine($data) && $this->shouldSkipHeadingLines()) {
                continue;
            }

            yield $data[$this->getLineKeyName()] => $data;
        }

        $this->fireFinishEvent();
    }

    /**
     * Get all rows of the file by the given path.
     *
     * @param string $path
     * @return array
     */
    public function all(string $path): array
    {
        return iterator_to_array($this->forEach($path));
    }

    /**
     * Returns the lines count if the setting is enabled or null.
     *
     * @param string $path
     * @return int|null
     */
    protected function prepareLinesCount(string $path): ?int
    {
        if (! $this->shouldGetLinesCount()) {
            return null;
        }

        return $this->fileReader->getLinesCount($path);
    }

    /**
     * Determine whether the given line is the heading row.
     */
    protected function isHeadingLine(array $line): bool
    {
        return array_keys($line)[0] === array_values($line)[0];
    }

    /**
     * Parse the given line.
     */
    protected function parseLine(string $line): array
    {
        $mappedLine = $this->mapLine($line);

        foreach ($mappedLine as $key => $value) {
            $mappedLine[$key] = $this->transformValue($value);
        }

        return $mappedLine;
    }

    /**
     * Map the given line into keyable array.
     *
     * @param string $line
     * @return array
     */
    protected function mapLine(string $line): array
    {
        return array_combine($this->fieldsMapping(), explode("\t", $line));
    }

    /**
     * Transform the given value into typed variable.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function transformValue($value)
    {
        return $value === '' ? null : $value;
    }
}
