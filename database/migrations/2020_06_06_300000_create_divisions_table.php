<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Division;

class CreateDivisionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(Division::TABLE, static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');

            if (app(Geonames::class)->shouldSupplyCountries()) {
                $table->foreignUuid('country_id')->index()->references('id')->on(Country::TABLE)->cascadeOnDelete();
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Division::TABLE);
    }
}
