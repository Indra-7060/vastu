<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('accessory_packages')->nullable()->after('specifications');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('package_key', 50)->nullable()->after('size');
            $table->string('package_label', 120)->nullable()->after('package_key');
            $table->decimal('package_price', 10, 2)->nullable()->after('package_label');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('package_label', 120)->nullable()->after('size');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('package_label');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['package_key', 'package_label', 'package_price']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('accessory_packages');
        });
    }
};
