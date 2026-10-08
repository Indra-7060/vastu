<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Review ratings with one decimal place (e.g. 4.3, 4.5).
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE customer_reviews MODIFY rating DECIMAL(2,1) NOT NULL DEFAULT 5.0');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE customer_reviews MODIFY rating TINYINT UNSIGNED NOT NULL DEFAULT 5');
    }
};
