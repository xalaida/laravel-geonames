<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Nevadskiy\Geonames\Models\City;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Timezone;

class CreateCitiesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(City::TABLE, static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('name_ascii');
            $table->uuid('country_id');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->bigInteger('population')->unsigned()->nullable();
            $table->smallInteger('elevation')->unsigned()->nullable()->comment('In meters.');
            $table->smallInteger('dem')->nullable()->comment('Digital elevation model, srtm3 or gtopo30.');
            $table->uuid('timezone_id');
            $table->string('feature_code')->nullable(); // TODO: check which city has nullable feature code
            $table->integer('geoname_id')->unsigned()->index();
            $table->timestamps();

            $table->foreign('timezone_id')->references('id')->on(Timezone::TABLE);
            $table->foreign('country_id')->references('id')->on(Country::TABLE);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(City::TABLE);
    }
}
