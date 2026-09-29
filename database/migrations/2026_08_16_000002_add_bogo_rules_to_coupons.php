<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->unsignedInteger('bogo_buy_quantity')->nullable()->after('min_quantity');
            $table->unsignedInteger('bogo_get_quantity')->nullable()->after('bogo_buy_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['bogo_buy_quantity', 'bogo_get_quantity']);
        });
    }
};
