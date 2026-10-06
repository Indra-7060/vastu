<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// The gallery now has categories; photos from the old single "Gallery Photos" section go to "Others".
return new class extends Migration
{
    public function up(): void
    {
        DB::table('banners')->where('section', 'gallery')->update(['section' => 'gallery_others']);
    }

    public function down(): void
    {
        DB::table('banners')->where('section', 'gallery_others')->update(['section' => 'gallery']);
    }
};
