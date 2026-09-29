<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('free_shipping_threshold', 10, 2)->default(899);
            $table->decimal('flat_shipping_rate', 10, 2)->default(60);
            $table->timestamps();
        });

        DB::table('shipping_settings')->insert([
            'free_shipping_threshold' => 899,
            'flat_shipping_rate' => 60,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_settings');
    }
};
