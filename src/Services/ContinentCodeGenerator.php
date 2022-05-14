<?php

namespace Nevadskiy\Geonames\Services;

class ContinentCodeGenerator
{
    /**
     * Generate a continent code by the given name.
     */
    public function generate(string $name): string
    {
        if (! str_contains($name, ' ')) {
            return $this->format($name);
        }

        return $this->format($this->getAbbr($name));
    }

    /**
     * Format the given name.
     */
    protected function format(string $name): string
    {
        return strtoupper(substr($name, 0, 2));
    }

    /**
     * Get an abbreviation from the given name.
     */
    protected function getAbbr(string $name): string
    {
        $abbr = '';

        foreach (explode(' ', $name) as $part) {
            $abbr .= $part[0];
        }

        return $abbr;
    }
}
