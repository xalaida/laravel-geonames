<?php

namespace Nevadskiy\Geonames\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Timezone;
use Nevadskiy\Geonames\Models\Country as CountryModel;
use Nevadskiy\Geonames\Nova\Traits\ReadOnlyResource;

class Country extends Resource
{
    use ReadOnlyResource;

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
     * The number of resources to show per page via relationships.
     *
     * @var int
     */
    public static $perPageViaRelationship = 20;

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
                ->onlyOnDetail(),

            Text::make(__('Code'), 'code')
                ->sortable(),

            Text::make(__('ISO'), 'iso')
                ->sortable(),

            Text::make(__('ISO numeric'), 'iso_numeric')
                ->sortable(),

            Text::make(__('Name'), 'name')
                ->sortable(),

            Text::make(__('Official name'), 'name_official')
                ->onlyOnDetail(),

            Number::make(__('Latitude'), 'latitude')
                ->sortable(),

            Number::make(__('Longitude'), 'longitude')
                ->sortable(),

            Timezone::make(__('Timezone ID'), 'timezone_id')
                ->onlyOnDetail(),

            BelongsTo::make(__('Continent'), 'continent')
                ->sortable(),

            Text::make(__('Capital'), 'capital')
                ->sortable(),

            Text::make(__('Currency code'), 'currency_code')
                ->sortable(),

            Text::make(__('Currency name'), 'currency_name')
                ->sortable(),

            Text::make(__('TLD'), 'tld')
                ->onlyOnDetail(),

            Text::make(__('Phone code'), 'phone_code')
                ->sortable(),

            Text::make(__('Postal code format'), 'postal_code_format')
                ->onlyOnDetail(),

            Text::make(__('Postal code regex'), 'postal_code_regex')
                ->onlyOnDetail(),

            Text::make(__('Languages'), 'languages')
                ->onlyOnDetail(),

            Text::make(__('Neighbours'), 'neighbours')
                ->onlyOnDetail(),

            Number::make(__('Area'), 'area')
                ->sortable(),

            Text::make(__('Fips'))
                ->onlyOnDetail(),

            Number::make(__('Population'), 'population')
                ->sortable(),

            Number::make(__('Dem'), 'dem')
                ->sortable(),

            Text::make(__('Feature code'), 'feature_code')
                ->sortable(),

            Number::make(__('Geoname ID'), 'geoname_id')
                ->sortable(),

            Date::make(__('Date of modification'), 'modified_at')
                ->onlyOnDetail(),

            DateTime::make(__('Date of creation'), 'created_at')
                ->onlyOnDetail(),

            DateTime::make(__('Date of update'), 'created_at')
                ->onlyOnDetail(),

            HasMany::make(__('Divisions'), 'divisions', Division::class),

            HasMany::make(__('Cities'), 'cities', City::class),
        ];
    }
}
