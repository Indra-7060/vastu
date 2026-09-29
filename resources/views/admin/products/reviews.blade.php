@extends('admin.layouts.app')

@section('title', 'Product Reviews - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ route('admin.products.edit', $product) }}" class="back-link">← Back to Product</a>
        <h2>Reviews — {{ $product->title }}</h2>
    </div>
</div>

<div class="card" style="margin-bottom:18px;">
    <h3 class="section-title">Add Review</h3>
    <form method="POST" action="{{ route('admin.products.reviews.store', $product) }}" class="inline-form" id="review-form" novalidate>
        @csrf
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="reviewer_name" value="{{ old('reviewer_name') }}" placeholder="Reviewer name">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="reviewer_email" value="{{ old('reviewer_email') }}" placeholder="Optional email">
        </div>
        <div class="form-group">
            <label>Rating (1–5)</label>
            <select name="rating">
                @for($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" @selected(old('rating', 5) == $i)>{{ $i }} ★</option>
                @endfor
            </select>
        </div>
        <div class="form-group" style="grid-column:1/-1;">
            <label>Comment</label>
            <textarea name="comment" rows="3" placeholder="Review comment">{{ old('comment') }}</textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Add Review</button>
        </div>
    </form>
</div>

<div class="card">
    <h3 class="section-title">All Reviews ({{ $reviews->total() }})</h3>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Reviewer</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                    <tr>
                        <td>{{ $reviews->firstItem() + $loop->index }}</td>
                        <td>
                            <strong>{{ $review->reviewer_name }}</strong>
                            @if($review->reviewer_email)
                                <br><small>{{ $review->reviewer_email }}</small>
                            @endif
                        </td>
                        <td><span class="rating-stars" aria-label="{{ $review->rating }} out of 5">@for($i = 1; $i <= 5; $i++)<span class="{{ $i <= $review->rating ? 'is-on' : '' }}">★</span>@endfor</span></td>
                        <td>{{ \Illuminate\Support\Str::limit($review->comment, 80) }}</td>
                        <td><span class="dash-badge {{ $review->is_active ? 'dash-badge--success' : 'dash-badge--neutral' }}">{{ $review->is_active ? 'Visible' : 'Hidden' }}</span></td>
                        <td>{{ $review->created_at->format('d M Y') }}</td>
                        <td class="table-actions">
                            <form method="POST" action="{{ route('admin.products.reviews.toggle', [$product, $review]) }}">
                                @csrf
                                @method('PATCH')
                                <label class="switch" title="Show / Hide">
                                    <input type="checkbox" onchange="this.form.submit()" {{ $review->is_active ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </form>
                            <form method="POST" action="{{ route('admin.products.reviews.destroy', [$product, $review]) }}" class="js-delete-form" data-confirm-title="Delete this review?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-sq action-del" title="Delete" aria-label="Delete">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">No reviews yet for this product.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        {{ $reviews->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('review-form') && window.FormValidator) {
        FormValidator.init('#review-form', {
            reviewer_name: [
                { type: 'required', message: 'Reviewer name is required.' },
            ],
            rating: [
                { type: 'required', message: 'Please select a rating.' },
            ],
        });
    }
});
</script>
@endpush
