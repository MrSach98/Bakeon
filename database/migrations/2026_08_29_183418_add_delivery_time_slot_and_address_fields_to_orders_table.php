<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'delivery_time_slot')) {
                $table->string('delivery_time_slot')->nullable()->after('delivery_date');
            }
            if (! Schema::hasColumn('orders', 'receiver_name')) {
                $table->string('receiver_name')->nullable()->after('customer_email');
            }
            if (! Schema::hasColumn('orders', 'receiver_phone')) {
                $table->string('receiver_phone')->nullable()->after('receiver_name');
            }
            if (! Schema::hasColumn('orders', 'alternate_phone')) {
                $table->string('alternate_phone')->nullable()->after('receiver_phone');
            }
            if (! Schema::hasColumn('orders', 'address_type')) {
                $table->string('address_type')->nullable()->after('city');
            }
            if (! Schema::hasColumn('orders', 'cake_message')) {
                $table->text('cake_message')->nullable()->after('delivery_time_slot');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = ['delivery_time_slot', 'receiver_name', 'receiver_phone', 'alternate_phone', 'address_type', 'cake_message'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};