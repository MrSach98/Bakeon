<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_delivery_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('delivery_option_id')->constrained('delivery_options')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'delivery_option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_delivery_options');
    }
};