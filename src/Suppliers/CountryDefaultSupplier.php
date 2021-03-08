<?php

namespace Nevadskiy\Geonames\Suppliers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Models\Country;

class CountryDefaultSupplier extends DefaultSupplier implements CountrySupplier
{
    /**
     * The country information list.
     *
     * @var array
     */
    protected $countryInfos;

    /**
     * The continents collection.
     *
     * @var Collection
     */
    protected $continents;

    /**
     * Make a new seeder instance.
     */
    public function __construct(array $countryInfos = [])
    {
        $this->countryInfos = $countryInfos;
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        parent::init();
        $this->continents = $this->getContinents();
    }

    /**
     * @inheritDoc
     */
    public function setCountryInfos(array $countryInfo): void
    {
        $this->countryInfos = $countryInfo;
    }

    /**
     * @inheritDoc
     */
    protected function shouldSupply(array $data, int $id): bool
    {
        return isset($this->countryInfos[$id]);
    }

    /**
     * @inheritDoc
     */
    protected function performInsert(array $data, int $id): bool
    {
        Country::query()->create(
            $this->resolveValues($this->mapInsertFields($data, $id))
        );

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function findModel(int $id): ?Model
    {
        return Country::query()
            ->where('geoname_id', $id)
            ->first();
    }

    /**
     * @inheritDoc
     */
    protected function updateModel(Model $model, array $data, int $id): bool
    {
        return $model->update(
            $this->resolveValues($this->mapUpdateFields($data, $id))
        );
    }

    /**
     * @inheritDoc
     */
    protected function deleteModel(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * Map insert for the country model.
     *
     * @param array $data
     * @return array
     */
    private function mapInsertFields(array $data, int $id): array
    {
        return $this->mapFields($data, $id);
    }

    /**
     * Map update fields for the country model.
     *
     * @param array $data
     * @return array
     */
    private function mapUpdateFields(array $data, int $id): array
    {
        return Arr::except($this->mapFields($data, $id), ['geoname_id']);
    }

    /**
     * Get continents collection grouped by code.
     */
    protected function getContinents(): Collection
    {
        return Continent::all()->keyBy('code');
    }

    /**
     * Map all fields for the country model.
     *
     * @param array $data
     * @param int $id
     * @return array
     */
    private function mapFields(array $data, int $id): array
    {
        return array_merge(
            $this->mapCountryInfoFields($this->countryInfos[$id]),
            $this->mapCountryFields($data, $id)
        );
    }

    /**
     * Map country table fields.
     *
     * @param array $data
     * @param int $id
     * @return array
     */
    private function mapCountryFields(array $data, int $id): array
    {
        return [
            'name_official' => $data['asciiname'] ?: $data['name'],
            'timezone_id' => $data['timezone'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'population' => $data['population'],
            'dem' => $data['dem'],
            'feature_code' => $data['feature code'],
            'geoname_id' => $id,
            'modified_at' => $data['modification date'],
        ];
    }

    /**
     * Map country info table fields.
     *
     * @param array $data
     * @return array
     */
    private function mapCountryInfoFields(array $data): array
    {
        return [
            'code' => $data['ISO'],
            'iso' => $data['ISO3'],
            'iso_numeric' => $data['ISO-Numeric'],
            'name' => $data['Country'],
            'continent_id' => function () use ($data) {
                return $this->continents[$data['Continent']]->id;
            },
            'capital' => $data['Capital'],
            'currency_code' => $data['CurrencyCode'],
            'currency_name' => $data['CurrencyName'],
            'tld' => $data['tld'],
            'phone_code' => $data['Phone'],
            'postal_code_format' => $data['Postal Code Format'],
            'postal_code_regex' => $data['Postal Code Regex'],
            'languages' => $data['Languages'],
            'neighbours' => $data['neighbours'],
            'area' => $data['Area(in sq km)'],
            'fips' => $data['fips'],
            'geoname_id' => $data['geonameid'],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getTableName(): string
    {
        return Country::TABLE;
    }
}
