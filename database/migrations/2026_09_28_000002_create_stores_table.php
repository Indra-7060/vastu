<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('Store');
            $table->string('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('pincode', 12)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email')->nullable();
            $table->string('opening_hours')->nullable();
            $table->string('services')->nullable();
            $table->string('map_url', 500)->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
