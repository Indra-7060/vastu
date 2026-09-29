<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('highlights_image')->nullable()->after('size_guide_image');
            $table->text('highlights_short_description')->nullable()->after('highlights_image');
            $table->json('highlights_items')->nullable()->after('highlights_short_description');
            $table->json('information_items')->nullable()->after('highlights_items');
            $table->json('specifications')->nullable()->after('information_items');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'highlights_image',
                'highlights_short_description',
                'highlights_items',
                'information_items',
                'specifications',
            ]);
        });
    }
};
