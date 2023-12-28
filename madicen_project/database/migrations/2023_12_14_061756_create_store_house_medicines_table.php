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
        Schema::create('store_house_medicines', function (Blueprint $table) {
            $table->id();
            $table->string('scientificname');
            $table->string('commercialname');
            $table->string('category');
            $table->string('company');
            $table->integer('quntity');
            $table->string('expirationdate');
            $table->integer('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_house_medicines');
    }
};
