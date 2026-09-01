<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('addon_id')->nullable()->after('product_weight_id')->constrained('addons')->cascadeOnDelete();

            // A cart row is now either a cake variant OR a standalone addon — make product columns nullable
            $table->foreignId('product_id')->nullable()->change();
            $table->foreignId('product_weight_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['addon_id']);
            $table->dropColumn('addon_id');
        });
    }
};