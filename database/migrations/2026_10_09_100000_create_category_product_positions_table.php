<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Admin → Products Master → Arrange Products: the order of products on each category page, and
// products from another category that are also shown on this category's page (is_extra).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_product_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->nullable();
            $table->boolean('is_extra')->default(false);
            $table->timestamps();
            $table->unique(['category_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_product_positions');
    }
};
