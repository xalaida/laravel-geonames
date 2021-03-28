<?php

namespace Nevadskiy\Geonames\Tests\Support\Utils\Fixtures;

use Illuminate\Foundation\Testing\WithFaker;
use Nevadskiy\Geonames\Tests\Support\Utils\FixtureFileBuilder;

class CountryInfoFixture
{
    use WithFaker;

    /**
     * @var FixtureFileBuilder
     */
    private $builder;

    /**
     * DailyDeletesFixture constructor.
     */
    public function __construct(FixtureFileBuilder $builder)
    {
        $this->builder = $builder;
        $this->setUpFaker();
    }

    /**
     * Create fixture file from the given data.
     *
     * @param array $data
     * @return string
     */
    public function create(array $data, string $filename = 'country-info.txt'): string
    {
        return $this->builder->build($filename, $this->mergeData($data));
    }

    /**
     * Merge data with default attributes.
     *
     * @param array $data
     * @return array|array[]
     */
    protected function mergeData(array $data): array
    {
        return array_map(function ($row) {
            return array_merge($this->defaults(), $row);
        }, $data);
    }

    /**
     * Get default attributes.
     *
     * @return array
     */
    protected function defaults(): array
    {
        return [
            'ISO' => 'AE',
            'ISO3' => 'ARE',
            'ISO-Numeric' => '784',
            'fips' => 'AE',
            'Country' => 'United Arab Emirates',
            'Capital' => 'Abu Dhabi',
            'Area(in sq km)' => '82880',
            'Population' => '9630959',
            'Continent' => 'AS',
            'tld' => '.ae',
            'CurrencyCode' => 'AED',
            'CurrencyName' => 'Dirham',
            'Phone' => '',
            'Postal Code Format' => '',
            'Postal Code Regex' => '',
            'Languages' => 'ar-AE,fa,en,hi,ur',
            'geonameid' => '290557',
            'neighbours' => 'SA,OM',
            'EquivalentFipsCode' => '',
        ];
    }
}
