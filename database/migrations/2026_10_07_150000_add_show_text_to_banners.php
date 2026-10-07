<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Hero slides: "Show text & buttons on this slide". Off = picture only (the whole slide links to its URL);
// on = the website draws the label, heading and buttons over the picture / video.
// Existing video slides start with their text shown; picture slides stay picture-only.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->boolean('show_text')->default(false)->after('is_active');
        });

        $videoBanners = DB::table('banner_images')
            ->where(function ($q) {
                foreach (['mp4', 'webm', 'ogg', 'ogv', 'mov'] as $ext) {
                    $q->orWhere('image', 'like', '%.'.$ext);
                }
            })
            ->pluck('banner_id');
        DB::table('banners')->where('section', 'home_hero')->whereIn('id', $videoBanners)->update(['show_text' => true]);
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('show_text');
        });
    }
};
