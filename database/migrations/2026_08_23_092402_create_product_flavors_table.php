<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_flavors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('flavor_id')->constrained('flavors')->cascadeOnDelete();
            $table->decimal('price_modifier', 10, 2)->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['product_id', 'flavor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_flavors');
    }
};