<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Category is required, Subcategory and Child Category are optional
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('child_category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique()->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            $table->decimal('base_price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();

            $table->enum('egg_type', ['egg', 'eggless', 'both'])->default('eggless');

            $table->boolean('is_photo_cake')->default(false);
            $table->boolean('is_message_enabled')->default(true);
            $table->unsignedInteger('message_char_limit')->default(30);

            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_bestseller')->default(false);
            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};