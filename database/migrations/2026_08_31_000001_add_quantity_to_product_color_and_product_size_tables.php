<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('product_color', 'quantity')) {
            Schema::table('product_color', function (Blueprint $table) {
                $table->unsignedInteger('quantity')->default(0)->after('color_id');
            });
        }

        if (! Schema::hasColumn('product_size', 'quantity')) {
            Schema::table('product_size', function (Blueprint $table) {
                $table->unsignedInteger('quantity')->default(0)->after('size_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_color', 'quantity')) {
            Schema::table('product_color', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }

        if (Schema::hasColumn('product_size', 'quantity')) {
            Schema::table('product_size', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
    }
};
