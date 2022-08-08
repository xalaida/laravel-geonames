<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique();
            $table->string('iso', 3)->unique();
            $table->string('iso_numeric', 3)->unique();
            $table->string('name');
            $table->string('name_official');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('timezone_id', 32)->nullable()->index();
            $table->foreignId('continent_id')->index()->references('id')->on('continents')->cascadeOnDelete();
            $table->string('capital')->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->string('currency_name', 32)->nullable();
            $table->string('tld', 3)->nullable();
            $table->string('phone_code', 24)->nullable();
            $table->string('postal_code_format', 100)->nullable();
            $table->string('postal_code_regex')->nullable();
            $table->string('languages')->nullable();
            $table->string('neighbours')->nullable();
            $table->float('area', 12, 2)->nullable()->unsigned()->comment('In square kilometers');
            $table->string('fips', 2)->nullable()->comment('Subject to change to iso code');
            $table->bigInteger('population')->unsigned()->nullable();
            $table->integer('elevation')->unsigned()->nullable()->comment('In meters');
            $table->smallInteger('dem')->nullable()->comment('Digital elevation model, srtm3 or gtopo30');
            $table->string('feature_code', 10)->nullable()->comment('See: https://www.geonames.org/export/codes.html');
            $table->integer('geoname_id')->unsigned()->nullable()->unique()->comment('Geonames database identifier');
            $table->timestamps();

            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
