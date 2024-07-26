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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('country_id')->index()->references('id')->on('countries')->cascadeOnDelete();
            $table->foreignId('division_id')->index()->nullable()->references('id')->on('divisions')->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('timezone_id', 32)->nullable()->index();
            $table->bigInteger('population')->unsigned()->nullable();
            $table->integer('elevation')->nullable()->comment('In meters');
            $table->smallInteger('dem')->nullable()->comment('Digital elevation model, srtm3 or gtopo30');
            $table->string('feature_code', 10)->nullable()->index()->comment('See: https://www.geonames.org/export/codes.html');
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
        Schema::dropIfExists('cities');
    }
};
