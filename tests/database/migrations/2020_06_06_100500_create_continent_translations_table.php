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
        Schema::create('continent_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('continent_id')->references('id')->on('continents')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_preferred');
            $table->boolean('is_short');
            $table->boolean('is_colloquial');
            $table->boolean('is_historic');
            $table->string('locale', 24)->nullable()->comment('RFC 5646. See: https://www.rfc-editor.org/rfc/rfc5646.txt.');
            $table->integer('alternate_name_id')->unsigned()->unique()->comment('Geonames database identifier.');
            $table->timestamps();

            $table->index(['continent_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('continent_translations');
    }
};
