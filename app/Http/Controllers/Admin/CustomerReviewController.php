<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerReview;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Admin → Home Content → Customer Reviews: the reviews (and their photos) on the home page. */
class CustomerReviewController extends Controller
{
    public function index()
    {
        $reviews = CustomerReview::query()->ordered()->paginate(20);
        $summary = [
            'reviews_rating' => SiteSettings::get('reviews_rating'),
            'reviews_count' => SiteSettings::get('reviews_count'),
            'reviews_url' => SiteSettings::get('reviews_url'),
        ];

        return view('admin.reviews.index', compact('reviews', 'summary'));
    }

    public function create()
    {
        return view('admin.reviews.create', ['review' => new CustomerReview(['rating' => 5, 'is_active' => true, 'show_google' => true, 'review_date' => now()])]);
    }

    public function store(Request $request)
    {
        $review = new CustomerReview();
        $this->fill($request, $review);

        return redirect()->route('admin.reviews.index')->with('success', 'Review added.');
    }

    public function edit(CustomerReview $review)
    {
        return view('admin.reviews.edit', compact('review'));
    }

    public function update(Request $request, CustomerReview $review)
    {
        $this->fill($request, $review);

        return redirect()->route('admin.reviews.index')->with('success', 'Review updated.');
    }

    public function destroy(CustomerReview $review)
    {
        foreach (array_merge([$review->photo], (array) $review->images) as $path) {
            $this->deleteFile($path);
        }
        $review->delete();

        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted.');
    }

    public function toggleStatus(CustomerReview $review)
    {
        $review->update(['is_active' => ! $review->is_active]);

        return back()->with('success', 'Review status updated.');
    }

    /** The rating badge next to the reviews (rating, number of reviews, link). */
    public function summary(Request $request)
    {
        $data = $request->validate([
            'reviews_rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'reviews_count' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'reviews_url' => ['nullable', 'url', 'max:500'],
        ]);
        SiteSettings::save($data);

        return back()->with('success', 'Rating badge updated.');
    }

    private function fill(Request $request, CustomerReview $review): void
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'text' => ['required', 'string', 'max:2000'],
            'review_date' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:8192'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['string'],
        ], [
            'images.max' => 'Up to 10 product photos per review.',
        ]);

        $review->fill([
            'name' => $data['name'],
            'rating' => (int) $data['rating'],
            'text' => $data['text'],
            'review_date' => $data['review_date'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
            'show_google' => $request->boolean('show_google'),
        ]);

        // Reviewer's photo
        if ($request->boolean('remove_photo') && $review->photo) {
            $this->deleteFile($review->photo);
            $review->photo = null;
        }
        if ($request->hasFile('photo')) {
            $this->deleteFile($review->photo);
            $review->photo = $request->file('photo')->store('reviews', 'public');
        }

        // Product photos shared by the customer
        $images = array_values((array) $review->images);
        $remove = (array) ($data['remove_images'] ?? []);
        if ($remove) {
            foreach (array_intersect($images, $remove) as $path) {
                $this->deleteFile($path);
            }
            $images = array_values(array_diff($images, $remove));
        }
        foreach ((array) $request->file('images', []) as $file) {
            $images[] = $file->store('reviews/photos', 'public');
        }
        $review->images = array_slice($images, 0, 10) ?: null;

        $review->save();
    }

    private function deleteFile(?string $path): void
    {
        // starter photos in public/vastu are part of the site, never deleted
        if ($path && ! str_starts_with($path, 'vastu/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
