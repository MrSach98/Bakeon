<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_token', 64)->nullable();

            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_weight_id')->constrained('product_weights')->cascadeOnDelete();

            $table->unsignedInteger('quantity')->default(1);

            $table->enum('status', ['active', 'ordered'])->default('active');
            $table->unsignedBigInteger('order_id')->nullable();

            $table->timestamps();

            $table->index('guest_token');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};