<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('feed_enabled')->default(true)->after('default_delivery_charge');
            $table->string('feed_token', 64)->nullable()->after('feed_enabled');
            $table->string('feed_currency', 5)->default('INR')->after('feed_token');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['feed_enabled', 'feed_token', 'feed_currency']);
        });
    }
};