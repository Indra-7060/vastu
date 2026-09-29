@extends('admin.layouts.app')

@section('title', 'Edit Offer - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ route('admin.offers.index') }}" class="back-link">← Back</a>
        <h2>Edit Offer</h2>
    </div>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.offers.update', $offer) }}" enctype="multipart/form-data" class="offer-form" id="offer-form" novalidate data-image-required="0">
        @csrf
        @method('PUT')

        <div class="offer-form-row">
            <label class="offer-label">Title <span>:-</span></label>
            <div class="offer-field form-group">
                <input type="text" name="title" value="{{ old('title', $offer->title) }}" placeholder="Enter title">
            </div>
        </div>

        <div class="offer-form-row offer-form-row-top">
            <label class="offer-label">Description(Optional) <span>:-</span></label>
            <div class="offer-field form-group">
                <textarea name="description" rows="8" placeholder="Enter description">{{ old('description', $offer->description) }}</textarea>
            </div>
        </div>

        <div class="offer-form-row">
            <label class="offer-label">Discount in % <span>:-</span></label>
            <div class="offer-field form-group">
                <div class="discount-input">
                    <input type="number" name="discount_percent" value="{{ old('discount_percent', $offer->discount_percent) }}" min="1" max="100" placeholder="e.g. 20" inputmode="numeric">
                    <span class="discount-suffix">%</span>
                </div>
            </div>
        </div>

        <div class="offer-form-row offer-form-row-top">
            <div class="offer-label-wrap">
                <label class="offer-label">Select Image <span>:-</span></label>
                <p class="hint">(Recommended resolution: 470X266, 570x223)</p>
                <p class="hint">(Accept png, jpg, jpeg, PNG, JPG, JPEG image files)</p>
                <p class="hint">(Recommended jpg, jpeg for best compression ratio)</p>
            </div>
            <div class="offer-field form-group">
                <div class="offer-image-box">
                    <input type="file" name="image" id="offer-image-input" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
                    <div class="offer-image-preview" id="offer-image-preview">
                        @if($offer->image)
                            <img src="{{ asset('storage/'.$offer->image) }}" alt="{{ $offer->title }}">
                        @else
                            <span>@include('admin.partials.icon', ['name' => 'image', 'size' => 22])</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="offer-form-actions">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const input = document.getElementById('offer-image-input');
    const preview = document.getElementById('offer-image-preview');
    if (!input || !preview) return;
    input.addEventListener('change', function () {
        const file = input.files && input.files[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        preview.innerHTML = '<img src="' + url + '" alt="Preview">';
    });
})();
</script>
@endpush
