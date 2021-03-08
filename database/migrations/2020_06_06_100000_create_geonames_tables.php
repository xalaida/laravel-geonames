<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Nevadskiy\Geonames\Models\City;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Division;

class CreateGeonamesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->createContinentsTable();
        $this->createCountriesTable();
        $this->createDivisionsTable();
        $this->createCitiesTable();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Continent::TABLE);
        Schema::dropIfExists(Country::TABLE);
        Schema::dropIfExists(Division::TABLE);
        Schema::dropIfExists(City::TABLE);
    }

    /**
     * Create countries table.
     */
    private function createContinentsTable(): void
    {
        if (config('geonames.tables.continents')) {
            Schema::create(Continent::TABLE, static function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code', 2)->unique();
                $table->string('name', 50);
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->string('timezone_id', 32)->nullable()->index();
                $table->bigInteger('population')->unsigned()->nullable();
                $table->smallInteger('elevation')->unsigned()->nullable()->comment('In meters.');
                $table->smallInteger('dem')->nullable()->comment('Digital elevation model, srtm3 or gtopo30.');
                $table->string('feature_code', 10)->nullable()->comment('See: https://www.geonames.org/export/codes.html');
                $table->integer('geoname_id')->unsigned()->unique()->comment('Geonames database identifier.');
                $table->date('modified_at')->comment('Date of last modification in the geonames database.');
                $table->timestamps();
            });
        }
    }

    /**
     * Create countries table.
     */
    private function createCountriesTable(): void
    {
        if (config('geonames.tables.countries')) {
            Schema::create(Country::TABLE, static function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code', 2)->unique();
                $table->string('iso', 3)->unique();
                $table->string('iso_numeric', 3)->unique();
                $table->string('name');
                $table->string('name_official');
                $table->string('timezone_id', 32)->nullable()->index();
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);

                if (config('geonames.tables.continents')) {
                    $table->foreignUuid('continent_id')->references('id')->on(Continent::TABLE)->cascadeOnDelete();
                }

                $table->string('capital')->nullable(); // Can be normalized using separate table.
                $table->string('currency_code', 3)->nullable(); // Can be normalized using separate table.
                $table->string('currency_name', 32)->nullable(); // Can be normalized using separate table.
                $table->string('tld', 3)->nullable();
                $table->string('phone_code', 24)->nullable();
                $table->string('postal_code_format', 100)->nullable();
                $table->string('postal_code_regex')->nullable();
                $table->string('languages')->nullable(); // Can be normalized using separate table.
                $table->string('neighbours')->nullable(); // Can be normalized using separate table.
                $table->float('area')->unsigned()->comment('In square kilometers.');
                $table->string('fips', 2)->nullable()->comment('Subject to change to iso code.');
                $table->bigInteger('population')->unsigned()->nullable();
                $table->smallInteger('dem')->nullable()->comment('Digital elevation model, srtm3 or gtopo30.');
                $table->string('feature_code', 10)->nullable()->comment('See: https://www.geonames.org/export/codes.html');
                $table->integer('geoname_id')->unsigned()->unique()->comment('Geonames database identifier.');
                $table->date('modified_at')->comment('Date of last modification in the geonames database.');
                $table->timestamps();
            });
        }
    }

    /**
     * Create divisions table.
     */
    private function createDivisionsTable(): void
    {
        if (config('geonames.tables.divisions')) {
            Schema::create(Division::TABLE, static function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');

                if (config('geonames.tables.countries')) {
                    $table->foreignUuid('country_id')->references('id')->on(Country::TABLE)->cascadeOnDelete();
                }

                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->string('timezone_id', 32)->nullable()->index();
                $table->bigInteger('population')->unsigned()->nullable();
                $table->smallInteger('elevation')->unsigned()->nullable()->comment('In meters.');
                $table->smallInteger('dem')->nullable()->comment('Digital elevation model, srtm3 or gtopo30.');
                $table->string('code', 20)->comment('Geonames code of administrative division.');
                $table->string('feature_code', 10)->nullable()->comment('See: https://www.geonames.org/export/codes.html');
                $table->integer('geoname_id')->unsigned()->unique()->comment('Geonames database identifier.');
                $table->date('modified_at')->comment('Date of last modification in the geonames database.');
                $table->timestamps();
            });
        }
    }

    /**
     * Create divisions table.
     */
    private function createCitiesTable(): void
    {
        if (config('geonames.tables.cities')) {
            Schema::create(City::TABLE, static function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');

                if (config('geonames.tables.countries')) {
                    $table->foreignUuid('country_id')->references('id')->on(Country::TABLE)->cascadeOnDelete();
                }

                if (config('geonames.tables.divisions')) {
                    $table->foreignUuid('division_id')->nullable()->references('id')->on(Division::TABLE)->cascadeOnDelete();
                }

                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->string('timezone_id', 32)->nullable()->index();
                $table->bigInteger('population')->unsigned()->nullable();
                $table->smallInteger('elevation')->unsigned()->nullable()->comment('In meters.');
                $table->smallInteger('dem')->nullable()->comment('Digital elevation model, srtm3 or gtopo30.');
                $table->string('feature_code', 10)->nullable()->comment('See: https://www.geonames.org/export/codes.html');
                $table->integer('geoname_id')->unsigned()->unique()->comment('Geonames database identifier.');
                $table->date('modified_at')->comment('Date of last modification in the geonames database.');
                $table->timestamps();
            });
        }
    }
}
