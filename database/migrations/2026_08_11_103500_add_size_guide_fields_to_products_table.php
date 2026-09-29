<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('show_size_guide')->default(true)->after('is_new_arrival');
            $table->longText('size_guide_content')->nullable()->after('show_size_guide');
            $table->string('size_guide_image')->nullable()->after('size_guide_content');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['show_size_guide', 'size_guide_content', 'size_guide_image']);
        });
    }
};
