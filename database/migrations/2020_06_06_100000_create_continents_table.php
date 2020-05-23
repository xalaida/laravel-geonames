<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Nevadskiy\Geonames\Models\Continent;

class CreateContinentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(Continent::TABLE, static function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('Internal unique identifier.');
            $table->string('slug', 50)->unique()->comment('Human readable unique identifier.');
            $table->string('name', 50);
            $table->string('code', 2);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->bigInteger('population')->unsigned();
            $table->smallInteger('dem')->comment('Digital elevation model, srtm3 or gtopo30.');
            $table->integer('geoname_id')->unsigned()->index()->comment('Geonames database identifier.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Continent::TABLE);
    }
}
