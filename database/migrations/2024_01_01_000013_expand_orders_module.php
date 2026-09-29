<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_mode')->nullable()->after('payable_amount');
            $table->string('payment_id')->nullable()->after('payment_mode');
            $table->decimal('subtotal', 12, 2)->default(0)->after('payment_id');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('subtotal');
            $table->decimal('delivery_charge', 12, 2)->default(0)->after('discount_amount');
            $table->string('shipping_name')->nullable()->after('user_phone');
            $table->string('shipping_phone')->nullable()->after('shipping_name');
            $table->text('shipping_address')->nullable()->after('shipping_phone');
            $table->date('expected_delivery_date')->nullable()->after('ordered_at');
        });

        // SQLite may not support change(); set default via raw if needed — also update existing
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_title');
            $table->string('product_image')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('mrp', 12, 2)->default(0);
            $table->decimal('saving', 12, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->string('status')->default('placed');
            $table->timestamps();
        });

        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('logged_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_logs');
        Schema::dropIfExists('order_items');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_mode',
                'payment_id',
                'subtotal',
                'discount_amount',
                'delivery_charge',
                'shipping_name',
                'shipping_phone',
                'shipping_address',
                'expected_delivery_date',
            ]);
        });
    }
};
