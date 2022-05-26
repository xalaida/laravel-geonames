<?php

namespace App\Nova\Geo;

use App\Models\Geo\Continent as ContinentModel;
use App\Nova\ReadOnlyResource;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MergeValue;
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
 * @property ContinentModel resource
 * @todo add validation rules
 * @todo add translations relation
 */
class Continent extends Resource
{
    use PerformsTranslatableQueries;
    use ReadOnlyResource;

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
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

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
                ->sortable(),

            Text::make(__('Code'), 'code')
                ->sortable(),

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

            Number::make(__('Dem'), 'dem')
                ->sortable(),

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
            HasMany::make(__('Countries'), 'countries', Country::class),
        ]);
    }
}
