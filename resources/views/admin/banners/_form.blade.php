@php
    $isEdit = isset($banner) && $banner->exists;
    $selectedSection = old('section', $isEdit ? $banner->section : ($presetSection ?? ''));
    $hasOld = session()->hasOldInput();
    $activeChecked = $hasOld ? (bool) old('is_active') : ($isEdit ? $banner->is_active : true);
@endphp

<div class="card product-form-card">
    <h3 class="section-title">Where it appears</h3>

    <div class="offer-form-row">
        <label class="offer-label">Section <span>:-</span></label>
        <div class="offer-field form-group">
            <select name="section" id="banner-section">
                <option value="">--Select Section--</option>
                @foreach($sections as $key => $meta)
                    <option value="{{ $key }}" @selected($selectedSection === $key)
                        data-hint="{{ $meta['hint'] }}"
                        data-media="{{ $meta['media'] }}"
                        data-fields='@json($meta['fields'] ?? [])'>{{ $meta['label'] }} ({{ $meta['page'] }})</option>
                @endforeach
            </select>
            <p class="hint" id="section-hint" style="margin-top:8px;">Choose where this banner appears on the website.</p>
            <ul class="banner-fields-guide" id="section-fields" hidden></ul>
        </div>
    </div>

    <div class="toggle-row" style="margin-top:4px;">
        <span>Show on website</span>
        <label class="switch">
            <input type="checkbox" name="is_active" value="1" @checked($activeChecked)>
            <span class="slider"></span>
        </label>
    </div>
</div>

<div class="card product-form-card" id="instagram-card" data-show-for="instagram_post" hidden>
    <h3 class="section-title">Instagram post</h3>
    <div class="form-group">
        <label for="instagram-embed">Post / reel link or embed code</label>
        <textarea name="instagram_embed" id="instagram-embed" rows="5" placeholder="https://www.instagram.com/p/XXXXXXXXXXX/  — or paste the full embed code from Instagram (••• → Embed → Copy embed code)">{{ old('instagram_embed', $isEdit && $selectedSection === 'instagram_post' ? $banner->button_link : '') }}</textarea>
        @error('instagram_embed')<p class="field-error">{{ $message }}</p>@enderror
        <p class="hint">Paste a link like <code>https://www.instagram.com/reel/…</code> or the whole embed code — the post link is picked out automatically and the real post is shown on the homepage.</p>
    </div>
</div>

<div class="card product-form-card">
    <h3 class="section-title">Content</h3>

    <div class="offer-form-row">
        <label class="offer-label">Title <span>:-</span></label>
        <div class="offer-field form-group">
            <input type="text" name="title" value="{{ old('title', $isEdit ? $banner->title : '') }}" placeholder="e.g. Sacred Living by Vastutathastu" maxlength="255">
        </div>
    </div>

    <div class="offer-form-row" data-hide-for="instagram_post">
        <label class="offer-label">Subtitle <span>:-</span></label>
        <div class="offer-field form-group">
            <input type="text" name="subtitle" value="{{ old('subtitle', $isEdit ? $banner->subtitle : '') }}" placeholder="Optional small label, e.g. Personal Vedic Guidance" maxlength="255">
        </div>
    </div>

    <div class="offer-form-row offer-form-row-top" data-hide-for="instagram_post">
        <label class="offer-label">Description <span>:-</span></label>
        <div class="offer-field form-group">
            <textarea name="description" rows="4" maxlength="2000" placeholder="Optional paragraph shown with the heading">{{ old('description', $isEdit ? $banner->description : '') }}</textarea>
        </div>
    </div>

    <div class="product-pricing-row" data-hide-for="instagram_post">
        <div class="form-group">
            <label>Button 1 text</label>
            <input type="text" name="button_text" value="{{ old('button_text', $isEdit ? $banner->button_text : '') }}" placeholder="e.g. Shop now" maxlength="100">
        </div>
        <div class="form-group">
            <label>Button 1 link</label>
            <input type="text" name="button_link" value="{{ old('button_link', $isEdit ? $banner->button_link : '') }}" placeholder="e.g. /shop or /rudraksha" maxlength="500">
        </div>
    </div>

    <div class="product-pricing-row" style="margin-top:12px;">
        <div class="form-group" data-hide-for="instagram_post">
            <label>Button 2 text</label>
            <input type="text" name="button_text_2" value="{{ old('button_text_2', $isEdit ? $banner->button_text_2 : '') }}" placeholder="Optional, e.g. Book a consultation" maxlength="100">
        </div>
        <div class="form-group" data-hide-for="instagram_post">
            <label>Button 2 link</label>
            <input type="text" name="button_link_2" value="{{ old('button_link_2', $isEdit ? $banner->button_link_2 : '') }}" placeholder="Optional link" maxlength="500">
        </div>
        <div class="form-group">
            <label>Display order</label>
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $isEdit ? $banner->sort_order : 0) }}">
        </div>
    </div>
</div>

<div class="card product-form-card" id="banner-media-card">
    <h3 class="section-title">Image / Video</h3>
    <p class="hint" id="banner-media-note" style="margin-bottom:14px;">Upload a JPG or PNG image (up to 4 MB).</p>

    @if($isEdit && $banner->images->count())
        <div class="banner-existing-list">
            @foreach($banner->images as $image)
                <div class="banner-existing-item">
                    <div class="banner-existing-preview">
                        @if($image->is_video)
                            <video src="{{ asset('storage/'.$image->image) }}" controls muted preload="metadata" style="width:100%;max-height:180px;object-fit:cover;"></video>
                        @else
                            <img src="{{ asset('storage/'.$image->image) }}" alt="">
                        @endif
                        <label class="remove-check">
                            <input type="checkbox" name="remove_images[]" value="{{ $image->id }}"> Remove
                        </label>
                    </div>
                    <div class="banner-existing-fields">
                        @if($image->mobile_image)
                            <div class="form-group">
                                <label>{{ $image->is_video ? 'Still image' : 'Mobile image' }}</label>
                                <img src="{{ asset('storage/'.$image->mobile_image) }}" alt="" style="max-width:160px;border-radius:6px;border:1px solid #e5e7eb;">
                            </div>
                        @endif
                        <div class="form-group">
                            <label>Order</label>
                            <input type="number" name="existing_sort[{{ $image->id }}]" min="0" value="{{ old('existing_sort.'.$image->id, $image->sort_order) }}">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="hint" style="margin-top:10px;">To replace the image, tick “Remove” and upload a new file below.</p>
    @endif

    <div class="form-group" style="margin-top:14px;">
        <label>{{ $isEdit ? 'Upload new file' : 'Upload file' }}</label>
        <input type="file" name="images[]" id="banner-images" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
        <div id="banner-image-preview" class="gallery-existing" style="margin-top:10px;"></div>
    </div>

    <div id="banner-image-meta" class="banner-image-meta" style="margin-top:14px;"></div>
</div>

<div class="offer-form-actions">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('admin.banners.index') }}" class="btn btn-light">Cancel</a>
</div>
