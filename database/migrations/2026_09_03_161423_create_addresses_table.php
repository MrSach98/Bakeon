<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->string('alternate_phone')->nullable();
            $table->string('address_line');
            $table->string('area_locality');
            $table->string('pincode', 10);
            $table->string('city');
            $table->enum('address_type', ['home', 'office', 'others'])->default('home');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};