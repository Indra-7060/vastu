<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'user_email')) {
                $table->string('user_email')->nullable()->after('user_phone');
            }
            if (! Schema::hasColumn('orders', 'shipping_email')) {
                $table->string('shipping_email')->nullable()->after('shipping_phone');
            }
            if (! Schema::hasColumn('orders', 'shipping_city')) {
                $table->string('shipping_city')->nullable()->after('shipping_address');
            }
            if (! Schema::hasColumn('orders', 'shipping_state')) {
                $table->string('shipping_state')->nullable()->after('shipping_city');
            }
            if (! Schema::hasColumn('orders', 'shipping_pincode')) {
                $table->string('shipping_pincode', 20)->nullable()->after('shipping_state');
            }
            if (! Schema::hasColumn('orders', 'shipping_country')) {
                $table->string('shipping_country')->nullable()->after('shipping_pincode');
            }
            if (! Schema::hasColumn('orders', 'order_notes')) {
                $table->text('order_notes')->nullable()->after('shipping_country');
            }
            if (! Schema::hasColumn('orders', 'razorpay_order_id')) {
                $table->string('razorpay_order_id')->nullable()->after('payment_id');
            }
            if (! Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->default('pending')->after('razorpay_order_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $cols = [
                'user_email',
                'shipping_email',
                'shipping_city',
                'shipping_state',
                'shipping_pincode',
                'shipping_country',
                'order_notes',
                'razorpay_order_id',
                'payment_status',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
