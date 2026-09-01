<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('site_settings', 'online_payment_enabled')) {
                $table->boolean('online_payment_enabled')->default(false)->after('currency_symbol');
            }
            if (! Schema::hasColumn('site_settings', 'free_delivery_threshold')) {
                $table->decimal('free_delivery_threshold', 10, 2)->default(199)->after('online_payment_enabled');
            }
            if (! Schema::hasColumn('site_settings', 'default_delivery_charge')) {
                $table->decimal('default_delivery_charge', 10, 2)->default(49)->after('free_delivery_threshold');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['online_payment_enabled', 'free_delivery_threshold', 'default_delivery_charge']);
        });
    }
};