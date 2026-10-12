<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Home — Hero video slides: an optional tall video played on phones instead of the wide desktop video. */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('banner_images', 'mobile_video')) {
            Schema::table('banner_images', function (Blueprint $table) {
                $table->string('mobile_video')->nullable()->after('mobile_image');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('banner_images', 'mobile_video')) {
            Schema::table('banner_images', function (Blueprint $table) {
                $table->dropColumn('mobile_video');
            });
        }
    }
};
