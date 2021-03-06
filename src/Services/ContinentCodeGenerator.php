<?php

namespace Nevadskiy\Geonames\Services;

use Nevadskiy\Geonames\Models\Continent;

class ContinentCodeGenerator
{
    /**
     * Generate a slug for the given continent.
     */
    public function generate(Continent $continent): string
    {
        if (! str_contains($continent->name, ' ')) {
            return $this->format($continent->name);
        }

        return $this->format($this->getAbbreviation($continent));
    }

    /**
     * Format the given slug.
     */
    private function format(string $slug): string
    {
        return strtoupper(substr($slug, 0, 2));
    }

    /**
     * Get an abbreviation of the continent.
     */
    private function getAbbreviation(Continent $continent): string
    {
        $slug = '';

        foreach (explode(' ', $continent->name) as $part) {
            $slug .= $part[0];
        }

        return $slug;
    }
}
