<?php

/*
|--------------------------------------------------------------------------
| Customer reviews (home page — Shree Yantra section)
|--------------------------------------------------------------------------
|
| Until real Google reviews are connected, the section shows the sample
| reviews below. To switch to live Google reviews, set in .env:
|
|   GOOGLE_PLACES_API_KEY=...   (Google Cloud key with "Places API (New)")
|   GOOGLE_PLACE_ID=...         (the business's Place ID)
|
| The latest, best-rated reviews are then fetched and cached; if Google
| can't be reached the sample reviews are shown instead.
|
*/

return [
    'google' => [
        'api_key' => env('GOOGLE_PLACES_API_KEY'),
        'place_id' => env('GOOGLE_PLACE_ID'),
        'cache_hours' => (int) env('GOOGLE_REVIEWS_CACHE_HOURS', 12),
    ],

    // Shown on the section's badge and "View all reviews" link when no live data is available.
    'summary' => [
        'rating' => 4.9,
        'count' => 120,
        'url' => env('GOOGLE_REVIEWS_URL', 'https://www.google.com/search?q=Vastutathastu+Pune+reviews'),
    ],

    // Temporary sample reviews (same as the Figma design).
    'sample' => [
        [
            'name' => 'Priya S.',
            'rating' => 5,
            'text' => 'The quality of the Shree Yantra is excellent. It brings a calm and positive energy to my home. Highly recommended!',
            'date' => '2025-08-12',
            'photo' => 'vastu/images/reviews/priya.jpg',
        ],
        [
            'name' => 'Rahul M.',
            'rating' => 5,
            'text' => 'Beautiful craftsmanship and premium finish. The energy in my workspace feels so much better after placing it.',
            'date' => '2025-06-28',
            'photo' => 'vastu/images/reviews/rahul.jpg',
        ],
        [
            'name' => 'Anjali P.',
            'rating' => 5,
            'text' => "I ordered the brass Shree Yantra and it's exactly as shown. Great packaging and timely delivery. Very satisfied!",
            'date' => '2025-05-14',
            'photo' => 'vastu/images/reviews/anjali.jpg',
        ],
    ],
];
