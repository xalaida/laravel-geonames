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

class Country extends Resource
{
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
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make(__('ID'), 'id')
                ->onlyOnDetail(),

            Text::make(__('Code'))
                ->sortable(),

            Text::make(__('ISO'))
                ->sortable(),

            Text::make(__('ISO numeric'))
                ->sortable(),

            Text::make(__('Name'))
                ->sortable(),

            Text::make(__('Official name'), 'name_official')
                ->onlyOnDetail(),

            Number::make(__('Latitude'))
                ->sortable(),

            Number::make(__('Longitude'))
                ->sortable(),

            Timezone::make(__('Timezone ID'))
                ->onlyOnDetail(),

            BelongsTo::make(__('Continent'))
                ->sortable(),

            Text::make(__('Capital'))
                ->sortable(),

            Text::make(__('Currency code'))
                ->sortable(),

            Text::make(__('Currency name'))
                ->sortable(),

            Text::make(__('TLD'))
                ->onlyOnDetail(),

            Text::make(__('Phone code'))
                ->sortable(),

            Text::make(__('Postal code format'))
                ->onlyOnDetail(),

            Text::make(__('Postal code regex'))
                ->onlyOnDetail(),

            Text::make(__('Languages'))
                ->onlyOnDetail(),

            Text::make(__('Neighbours'))
                ->onlyOnDetail(),

            Number::make(__('Area'))
                ->sortable(),

            Text::make(__('Fips'))
                ->onlyOnDetail(),

            Number::make(__('Population'))
                ->sortable(),

            Number::make(__('Dem'))
                ->sortable(),

            Text::make(__('Feature code'))
                ->sortable(),

            Number::make(__('Geoname ID'))
                ->sortable(),

            Date::make(__('Date of modification'), 'modified_at')
                ->onlyOnDetail(),

            DateTime::make(__('Date of creation'), 'created_at')
                ->onlyOnDetail(),

            DateTime::make(__('Date of update'), 'created_at')
                ->onlyOnDetail(),

            HasMany::make(__('Divisions')),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
