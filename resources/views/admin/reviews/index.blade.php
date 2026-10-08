@extends('admin.layouts.app')

@section('title', 'Customer Reviews - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <h2>Customer Reviews</h2>
        <p class="page-sub">Reviews shown beside the Shree Yantra on the home page — three at a time, with arrows for the rest. Click a review on the website to see the customer’s product photos.</p>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.reviews.create') }}" class="btn btn-primary">@include('admin.partials.icon', ['name' => 'plus', 'size' => 16]) Add Review</a>
    </div>
</div>

<div class="card product-form-card">
    <h3 class="section-title">Rating badge</h3>
    <p class="hint" style="margin:-4px 0 14px;">The box with the rating and “View all reviews” link next to the reviews.</p>
    <form method="POST" action="{{ route('admin.reviews.summary') }}" class="review-summary-form">
        @csrf
        <div class="form-group">
            <label>Rating out of 5</label>
            <input type="number" name="reviews_rating" step="0.1" min="0" max="5" value="{{ old('reviews_rating', $summary['reviews_rating']) }}">
        </div>
        <div class="form-group">
            <label>Number of reviews</label>
            <input type="number" name="reviews_count" min="0" value="{{ old('reviews_count', $summary['reviews_count']) }}">
        </div>
        <div class="form-group review-summary-form__wide">
            <label>“View all reviews” link</label>
            <input type="url" name="reviews_url" value="{{ old('reviews_url', $summary['reviews_url']) }}" placeholder="https://…">
        </div>
        <button type="submit" class="btn btn-primary">Save badge</button>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Photos</th>
                    <th>Date</th>
                    <th>Show</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                <tr>
                    <td>{{ $review->sort_order }}</td>
                    <td>
                        <div class="store-cell">
                            @if($review->photo_url)
                                <img src="{{ $review->photo_url }}" alt="" class="table-thumb" style="border-radius:50%;width:40px;height:40px;object-fit:cover;">
                            @else
                                <span class="store-cell__ico">{{ mb_strtoupper(mb_substr($review->name, 0, 1)) }}</span>
                            @endif
                            <span><strong>{{ $review->name }}</strong></span>
                        </div>
                    </td>
                    <td class="nowrap" style="color:#e2a400;">{{ str_repeat('★', $review->rating) }}<span style="color:#d1d5db;">{{ str_repeat('★', 5 - $review->rating) }}</span></td>
                    <td><small>{{ \Illuminate\Support\Str::limit($review->text, 90) }}</small></td>
                    <td>
                        @if($review->image_urls)
                            <div class="review-thumbs">
                                @foreach(array_slice($review->image_urls, 0, 3) as $url)<img src="{{ $url }}" alt="">@endforeach
                                @if(count($review->image_urls) > 3)<span>+{{ count($review->image_urls) - 3 }}</span>@endif
                            </div>
                        @else
                            <small>—</small>
                        @endif
                    </td>
                    <td class="nowrap"><small>{{ optional($review->review_date)->format('j M Y') ?: '—' }}</small></td>
                    <td>
                        <form method="POST" action="{{ route('admin.reviews.toggle', $review) }}">
                            @csrf
                            @method('PATCH')
                            <label class="switch" title="{{ $review->is_active ? 'Shown on website' : 'Hidden from website' }}">
                                <input type="checkbox" onchange="this.form.submit()" {{ $review->is_active ? 'checked' : '' }} aria-label="Shown on website">
                                <span class="slider"></span>
                            </label>
                        </form>
                    </td>
                    <td class="actions-cell">
                        <a href="{{ route('admin.reviews.edit', $review) }}" class="action-sq" title="Edit" aria-label="Edit review by {{ $review->name }}">@include('admin.partials.icon', ['name' => 'edit', 'size' => 16])</a>
                        <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" class="js-delete-form" data-confirm-title="Delete this review?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-sq action-del" title="Delete" aria-label="Delete review by {{ $review->name }}">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8"><div class="empty-state">No reviews yet. <a href="{{ route('admin.reviews.create') }}">Add the first review</a></div></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination-wrap">{{ $reviews->links() }}</div>
@endsection
