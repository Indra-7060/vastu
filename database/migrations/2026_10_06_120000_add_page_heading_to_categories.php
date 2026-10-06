<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Category page banner text, editable in Admin → Categories:
 * - page_heading: the big title on the category page (falls back to the category name)
 * - page_description: the line under it (falls back to the short description / default sentence)
 * Kept separate from the category name so editing them never changes menus or the page URL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'page_heading')) {
                $table->string('page_heading', 120)->nullable()->after('short_description');
            }
            if (! Schema::hasColumn('categories', 'page_description')) {
                $table->string('page_description', 500)->nullable()->after('page_heading');
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['page_heading', 'page_description']);
        });
    }
};
