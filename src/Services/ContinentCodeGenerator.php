<?php

namespace Nevadskiy\Geonames\Services;

class ContinentCodeGenerator
{
    /**
     * Generate a code for the given name.
     */
    public function generate(string $name): string
    {
        if (! str_contains($name, ' ')) {
            return $this->format($name);
        }

        return $this->format($this->getAbbreviation($name));
    }

    /**
     * Format the given name.
     */
    protected function format(string $name): string
    {
        return strtoupper(substr($name, 0, 2));
    }

    /**
     * Get an abbreviation by the given name.
     */
    protected function getAbbreviation(string $name): string
    {
        $slug = '';

        foreach (explode(' ', $name) as $part) {
            $slug .= $part[0];
        }

        return $slug;
    }
}
