<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Hero slides whose picture already contains the headline and button: the website then shows
// the picture on its own (no text on top) and makes the whole slide a link.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->boolean('text_in_image')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('text_in_image');
        });
    }
};
