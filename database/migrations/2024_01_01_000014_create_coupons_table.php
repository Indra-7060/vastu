<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->longText('description')->nullable();
            $table->enum('discount_type', ['percent', 'amount'])->default('percent');
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->boolean('max_discount_status')->default(false);
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->boolean('min_cart_status')->default(false);
            $table->decimal('min_cart_amount', 10, 2)->nullable();
            $table->boolean('max_use_per_user')->default(false);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
