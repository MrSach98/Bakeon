<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('addon_id')->constrained('addons')->cascadeOnDelete();
            $table->decimal('price_override', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'addon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_addons');
    }
};