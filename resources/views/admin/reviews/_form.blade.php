@php
    $hasOld = session()->hasOldInput();
    $activeChecked = $hasOld ? (bool) old('is_active') : (bool) $review->is_active;
    $googleChecked = $hasOld ? (bool) old('show_google') : (bool) $review->show_google;
@endphp

<div class="card product-form-card">
    <h3 class="section-title">Review</h3>
    <div class="store-form-grid">
        <div class="form-group">
            <label>Customer name *</label>
            <input type="text" name="name" value="{{ old('name', $review->name) }}" maxlength="120" placeholder="e.g. Priya S." required>
            @error('name')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="form-group">
            <label>Rating *</label>
            <select name="rating" required>
                @foreach([5, 4, 3, 2, 1] as $n)
                    <option value="{{ $n }}" @selected((int) old('rating', $review->rating) === $n)>{{ str_repeat('★', $n) }} ({{ $n }})</option>
                @endforeach
            </select>
        </div>
        <div class="form-group span-2">
            <label>Review text *</label>
            <textarea name="text" rows="5" maxlength="2000" placeholder="What the customer said" required>{{ old('text', $review->text) }}</textarea>
            @error('text')<p class="field-error">{{ $message }}</p>@enderror
            <p class="hint">The card shows the first few lines; the full text appears when a visitor clicks the review.</p>
        </div>
        <div class="form-group">
            <label>Review date</label>
            <input type="date" name="review_date" value="{{ old('review_date', optional($review->review_date)->format('Y-m-d')) }}">
        </div>
        <div class="form-group">
            <label>Display order</label>
            <input type="number" name="sort_order" min="0" max="9999" value="{{ old('sort_order', $review->sort_order ?? 0) }}">
            <p class="hint">Lower numbers are shown first.</p>
        </div>
    </div>
</div>

<div class="card product-form-card">
    <h3 class="section-title">Photos</h3>
    <div class="store-form-grid">
        <div class="form-group">
            <label>Customer photo</label>
            @if($review->photo_url)
                <div class="store-form-photo review-avatar-preview">
                    <img src="{{ $review->photo_url }}" alt="">
                    <label class="remove-check"><input type="checkbox" name="remove_photo" value="1"> Remove photo</label>
                </div>
            @endif
            <input type="file" name="photo" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
            <p class="hint">Optional, shown as a small round picture. Without one, the first letter of the name is shown.</p>
            @error('photo')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="form-group span-2">
            <label>Product photos from the customer</label>
            @if($review->images)
                <div class="review-photo-grid">
                    @foreach((array) $review->images as $path)
                        <label class="review-photo">
                            <img src="{{ \App\Models\CustomerReview::fileUrl($path) }}" alt="">
                            <span class="remove-check"><input type="checkbox" name="remove_images[]" value="{{ $path }}"> Remove</span>
                        </label>
                    @endforeach
                </div>
            @endif
            <input type="file" name="images[]" multiple accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
            <p class="hint">Optional — up to 10 photos the customer shared with the product. They open when a visitor clicks this review.</p>
            @error('images')<p class="field-error">{{ $message }}</p>@enderror
            @error('images.*')<p class="field-error">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="card product-form-card">
    <h3 class="section-title">Display</h3>
    <div class="toggle-row">
        <span>Show on website</span>
        <label class="switch"><input type="checkbox" name="is_active" value="1" @checked($activeChecked)><span class="slider"></span></label>
    </div>
    <div class="toggle-row">
        <span>Google review <small style="display:block;font-weight:400;color:#6b7280;">Shows the small Google logo on the card — only for reviews actually posted on Google.</small></span>
        <label class="switch"><input type="checkbox" name="show_google" value="1" @checked($googleChecked)><span class="slider"></span></label>
    </div>
</div>

<div class="offer-form-actions">
    <button type="submit" class="btn btn-primary">Save review</button>
    <a href="{{ route('admin.reviews.index') }}" class="btn btn-light">Cancel</a>
</div>
