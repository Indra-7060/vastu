<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Customer reviews written in Admin → Home Content → Customer Reviews (home page Shree Yantra section).
// Starts with the three reviews the site showed before, so nothing changes until the admin edits them.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('photo')->nullable();          // reviewer's photo (optional)
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('text');
            $table->date('review_date')->nullable();
            $table->json('images')->nullable();           // photos the customer shared with the product
            $table->boolean('show_google')->default(true); // small Google logo on the card
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        foreach ([
            ['Priya S.', 'vastu/images/reviews/priya.jpg', 'The quality of the Shree Yantra is excellent. It brings a calm and positive energy to my home. Highly recommended!', '2025-08-12'],
            ['Rahul M.', 'vastu/images/reviews/rahul.jpg', 'Beautiful craftsmanship and premium finish. The energy in my workspace feels so much better after placing it.', '2025-06-28'],
            ['Anjali P.', 'vastu/images/reviews/anjali.jpg', "I ordered the brass Shree Yantra and it's exactly as shown. Great packaging and timely delivery. Very satisfied!", '2025-05-14'],
        ] as $i => [$name, $photo, $text, $date]) {
            DB::table('customer_reviews')->insert([
                'name' => $name, 'photo' => $photo, 'rating' => 5, 'text' => $text, 'review_date' => $date,
                'images' => null, 'show_google' => true, 'is_active' => true, 'sort_order' => $i + 1,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_reviews');
    }
};
