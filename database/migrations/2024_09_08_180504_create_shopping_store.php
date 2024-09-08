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
        Schema::create('shopping_stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('postcode');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->boolean('is_open')->default(true);
            $table->unsignedBigInteger('store_type');
            $table->foreign('store_type')->references('id')
                ->on('shopping_store_types')->onDelete('cascade');
            $table->decimal('delivery_distance', 4, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopping_stores');
    }
};
