<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_phone')->nullable();
            $table->decimal('payable_amount', 12, 2)->default(0);
            $table->string('status')->default('pending'); // pending, processing, shipped, delivered, cancelled
            $table->timestamp('ordered_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'ordered_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
