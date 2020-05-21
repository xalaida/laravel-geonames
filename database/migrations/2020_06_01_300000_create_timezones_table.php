<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Timezone;

class CreateTimezonesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(Timezone::TABLE, static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('country_id');
            $table->decimal('offset_gmt', 3, 1);
            $table->decimal('offset_dst', 3, 1);
            $table->decimal('offset_raw', 3, 1);
            $table->timestamps();

            $table->foreign('country_id')->references('id')->on(Country::TABLE)->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Timezone::TABLE);
    }
}
