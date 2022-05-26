<?php

namespace App\Nova\Geo;

use App\Models\Geo\Country as CountryModel;
use App\Nova\ReadOnlyResource;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MergeValue;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Timezone;
use Nevadskiy\NovaTranslatable\PerformsTranslatableQueries;
use Nevadskiy\NovaTranslatable\Translatable;

/**
 * @property CountryModel resource
 * @todo add validation rules
 * @todo add translations relation
 */
class Country extends Resource
{
    use ReadOnlyResource;
    use PerformsTranslatableQueries;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = CountryModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
        'code',
    ];

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array
     */
    public static $with = [
        'continent',
    ];

    /**
     * Get the logical group associated with the resource.
     */
    public static function group(): string
    {
        return __('Geo');
    }

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            ID::make(__('ID'), 'id')
                ->sortable(),

            Text::make(__('Code'), 'code')
                ->sortable(),

            // TODO: add FLAG (preview) field

            Text::make(__('ISO'), 'iso')
                ->sortable(),

            Text::make(__('ISO numeric'), 'iso_numeric')
                ->hideFromIndex(),

            Translatable::fields(function (string $locale) {
                return [
                    Text::make(__('Name [:locale]', ['locale' => $locale]), 'name')
                        ->sortable()
                        ->showOnPreview(),
                ];
            })
                ->locales(
                    // TODO: refactor this to remove nullable from array.
                    collect(config('geonames.translations.locales'))
                        ->filter()
                        ->values()
                        ->all()
                )
                ->onlyCurrentOnIndex()
                ->create(),

            Text::make(__('Official name'), 'name_official')
                ->onlyOnDetail(),

            // TODO: replace with Location (map) field.
            Number::make(__('Latitude'), 'latitude')
                ->step(0.0000001)
                ->hideFromIndex(),

            Number::make(__('Longitude'), 'longitude')
                ->step(0.0000001)
                ->hideFromIndex(),

            Timezone::make(__('Timezone ID'), 'timezone_id')
                ->hideFromIndex(),

            BelongsTo::make(__('Continent'), 'continent', Continent::class)
                ->sortable()
                ->filterable(),

            Text::make(__('Capital'), 'capital')
                ->hideFromIndex(),

            Text::make(__('Currency code'), 'currency_code')
                ->hideFromIndex(),

            Text::make(__('Currency name'), 'currency_name')
                ->hideFromIndex(),

            Text::make(__('TLD'), 'tld')
                ->hideFromIndex(),

            Text::make(__('Phone code'), 'phone_code')
                ->hideFromIndex(),

            Text::make(__('Postal code format'), 'postal_code_format')
                ->hideFromIndex(),

            Text::make(__('Postal code regex'), 'postal_code_regex')
                ->hideFromIndex(),

            Text::make(__('Languages'), 'languages')
                ->hideFromIndex(),

            Text::make(__('Neighbours'), 'neighbours')
                ->hideFromIndex(),

            Number::make(__('Area'), 'area')
                ->hideFromIndex(),

            Text::make(__('Fips'))
                ->hideFromIndex(),

            Number::make(__('Population'), 'population')
                ->sortable(),

            Number::make(__('Elevation'), 'elevation')
                ->hideFromIndex(),

            Number::make(__('Dem'), 'dem')
                ->hideFromIndex(),

            Text::make(__('Feature code'), 'feature_code')
                ->hideFromIndex(),

            Number::make(__('Geoname ID'), 'geoname_id')
                ->hideFromIndex(),

            Date::make(__('Synced at'), 'synced_at')
                ->sortable(),

            DateTime::make(__('Created at'), 'created_at')
                ->hideFromIndex(),

            DateTime::make(__('Updated at'), 'updated_at')
                ->hideFromIndex(),

            $this->relations(),
        ];
    }

    /**
     * Get relations of the resource.
     */
    protected function relations(): MergeValue
    {
        return new MergeValue([
            HasMany::make(__('Divisions'), 'divisions', Division::class),

            HasMany::make(__('Cities'), 'cities', City::class),
        ]);
    }
}
