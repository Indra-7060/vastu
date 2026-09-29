<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('coupons', 'is_public')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->boolean('is_public')->default(true)->after('is_active');
            });
        }

        DB::table('coupons')->where('code', 'WELCOME40')->update(['is_public' => false]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('coupons', 'is_public')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropColumn('is_public');
            });
        }
    }
};
