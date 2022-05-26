<?php

namespace App\Nova\Geo;

use App\Models\Geo\City as CityModel;
use App\Nova\ReadOnlyResource;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Timezone;
use Nevadskiy\NovaTranslatable\PerformsTranslatableQueries;

/**
 * @property CityModel resource
 * @todo add validation rules
 * @todo add translations relation
 */
class City extends Resource
{
    use PerformsTranslatableQueries;
    use ReadOnlyResource;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = CityModel::class;

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
        'country',
        'division',
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

            Text::make(__('Name'), 'name')
                ->sortable(),

            BelongsTo::make(__('Country'), 'country', Country::class)
                ->filterable()
                ->searchable()
                ->sortable(),

            BelongsTo::make(__('Division'), 'division', Division::class)
                ->searchable()
                ->sortable(),

            // TODO: replace with Location (map) field.
            Number::make(__('Latitude'), 'latitude')
                ->step(0.0000001)
                ->hideFromIndex(),

            Number::make(__('Longitude'), 'longitude')
                ->step(0.0000001)
                ->hideFromIndex(),

            Timezone::make(__('Timezone ID'), 'timezone_id')
                ->onlyOnDetail(),

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
        ];
    }
}
