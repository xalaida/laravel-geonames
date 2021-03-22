<?php

namespace Nevadskiy\Geonames\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Timezone;
use Nevadskiy\Geonames\Models\Continent as ContinentModel;
use Nevadskiy\Geonames\Nova\Traits\ReadOnly;

class Continent extends Resource
{
    use ReadOnly;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = ContinentModel::class;

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

            Text::make(__('Name'), 'name')
                ->sortable(),

            Number::make(__('Latitude'), 'latitude')
                ->sortable(),

            Number::make(__('Longitude'), 'longitude')
                ->sortable(),

            Timezone::make(__('Timezone ID'), 'timezone_id')
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

            HasMany::make(__('Countries'), 'countries', Country::class),
        ];
    }
}
