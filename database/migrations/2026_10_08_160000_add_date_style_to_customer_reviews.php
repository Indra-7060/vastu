<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// How a review's date is shown: 'date' = "12 Aug 2025", 'ago' = "6 months ago" (like Google, kept up to date).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_reviews', function (Blueprint $table) {
            $table->string('date_style', 8)->default('date')->after('review_date');
        });
    }

    public function down(): void
    {
        Schema::table('customer_reviews', function (Blueprint $table) {
            $table->dropColumn('date_style');
        });
    }
};
