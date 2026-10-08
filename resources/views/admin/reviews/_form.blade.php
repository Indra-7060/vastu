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
            <input type="number" name="rating" min="1" max="5" step="0.1" value="{{ old('rating', rtrim(rtrim(number_format((float) $review->rating, 1), '0'), '.')) }}" required>
            <p class="hint">From 1 to 5, decimals allowed — e.g. 4.5 shows four and a half stars.</p>
            @error('rating')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="form-group span-2">
            <label>Review text *</label>
            <textarea name="text" rows="5" maxlength="2000" placeholder="What the customer said" required>{{ old('text', $review->text) }}</textarea>
            @error('text')<p class="field-error">{{ $message }}</p>@enderror
            <p class="hint">The card shows the first few lines; the full text appears when a visitor clicks the review.</p>
        </div>
        <div class="form-group span-2">
            @php
                $dateStyle = old('date_style', $review->date_style ?: 'date');
                $agoGuess = ['amount' => '', 'unit' => 'months'];
                if ($review->review_date) {
                    $days = $review->review_date->diffInDays(now());
                    $agoGuess = $days >= 365 ? ['amount' => intdiv($days, 365), 'unit' => 'years'] : ($days >= 30 ? ['amount' => intdiv($days, 30), 'unit' => 'months'] : ($days >= 7 ? ['amount' => intdiv($days, 7), 'unit' => 'weeks'] : ['amount' => max(1, $days), 'unit' => 'days']));
                }
            @endphp
            <label>Review date</label>
            <div class="review-date-style">
                <label class="review-date-style__opt"><input type="radio" name="date_style" value="date" @checked($dateStyle !== 'ago')> Exact date <small>e.g. 12 Aug 2025</small></label>
                <label class="review-date-style__opt"><input type="radio" name="date_style" value="ago" @checked($dateStyle === 'ago')> Time ago, like Google <small>e.g. 6 months ago</small></label>
            </div>
            <div class="review-date-row" data-date-pane="date" @if($dateStyle === 'ago') hidden @endif>
                <input type="date" name="review_date" max="{{ now()->format('Y-m-d') }}" value="{{ old('review_date', optional($review->review_date)->format('Y-m-d')) }}">
            </div>
            <div class="review-date-row" data-date-pane="ago" @if($dateStyle !== 'ago') hidden @endif>
                <input type="number" name="ago_amount" min="1" max="200" value="{{ old('ago_amount', $agoGuess['amount']) }}" placeholder="6" style="max-width:110px;">
                <select name="ago_unit" style="max-width:160px;">
                    @foreach(['days' => 'days ago', 'weeks' => 'weeks ago', 'months' => 'months ago', 'years' => 'years ago'] as $u => $l)
                        <option value="{{ $u }}" @selected(old('ago_unit', $agoGuess['unit']) === $u)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <p class="hint">“Time ago” is copied from Google (e.g. 6 months ago). The website keeps it up to date — next month it shows “7 months ago”.</p>
            @error('review_date')<p class="field-error">{{ $message }}</p>@enderror
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

<script>
    // Review date: switch between the date picker and the "time ago" fields
    document.querySelectorAll('input[name="date_style"]').forEach(function (r) {
        r.addEventListener('change', function () {
            document.querySelectorAll('[data-date-pane]').forEach(function (p) { p.hidden = p.getAttribute('data-date-pane') !== r.value; });
        });
    });
</script>
