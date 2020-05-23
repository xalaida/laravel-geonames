<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Models\Country;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(Country::TABLE, static function (Blueprint $table) {
            // TODO: add string limits
            // TODO: normalize structure
            $table->uuid('id')->primary()->comment('Internal unique identifier.');
            $table->string('slug')->unique()->comment('Human readable unique identifier.');
            $table->string('iso', 2);
            $table->string('iso3', 3);
            $table->string('iso_numeric', 3);
            $table->string('fips')->comment('Subject to change to iso code.');
            $table->string('name');
            $table->string('name_official');
            $table->string('capital'); // TODO: extract into separate table using foreign key
            $table->uuid('continent_id');
            $table->string('tld');
            $table->string('currency_code'); // TODO: can be refactored using separate
            $table->string('currency_name');
            $table->string('phone_code');
            $table->string('postal_code_format');
            $table->string('postal_code_regex');
            $table->string('languages'); // TODO: can be refactored using separate table
            $table->string('neighbours'); // TODO: can be refactored using separate table
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->float('area')->unsigned()->comment('In square kilometers.');
            $table->bigInteger('population')->unsigned();
            $table->smallInteger('dem')->comment('Digital elevation model, srtm3 or gtopo30.');
            $table->string('feature_code');
            $table->integer('geoname_id')->unsigned()->index();
            $table->timestamps();

            $table->foreign('continent_id')->references('id')->on(Continent::TABLE);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Country::TABLE);
    }
}
