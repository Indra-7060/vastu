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


    <div class="toggle-row banner-textimg" data-show-for="home_hero" hidden>
        <span>
            <strong>Show text &amp; buttons on this slide</strong>
            <small><b>Off</b> — picture only: upload a finished design (text and “Shop now” already in the image) and set where a click on the picture goes.<br><b>On</b> — the website shows its own small label, heading and buttons over the picture or video. Fill in only what you want shown; empty fields are simply not displayed.</small>
        </span>
        <label class="switch">
            <input type="checkbox" name="show_text" value="1" id="banner-show-text" @checked(old('show_text', $isEdit ? $banner->show_text : false))>
            <span class="slider"></span>
        </label>
    </div>

    <div class="offer-form-row" data-hide-for="home_hero" data-hero-text>
        <label class="offer-label">Title <span>:-</span></label>
        <div class="offer-field form-group">
            <input type="text" name="title" value="{{ old('title', ($isEdit && $banner->title !== 'Hero slide') ? $banner->title : '') }}" placeholder="e.g. Sacred Living by Vastutathastu" maxlength="255">
            @error('title')<p class="field-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="offer-form-row" data-hide-for="instagram_post home_hero" data-hero-text>
        <label class="offer-label">Subtitle <span>:-</span></label>
        <div class="offer-field form-group">
            <input type="text" name="subtitle" value="{{ old('subtitle', $isEdit ? $banner->subtitle : '') }}" placeholder="Optional small label, e.g. Personal Vedic Guidance" maxlength="255">
        </div>
    </div>

    <div class="offer-form-row offer-form-row-top" data-hide-for="instagram_post home_hero">
        <label class="offer-label">Description <span>:-</span></label>
        <div class="offer-field form-group">
            <textarea name="description" rows="4" maxlength="2000" placeholder="Optional paragraph shown with the heading">{{ old('description', $isEdit ? $banner->description : '') }}</textarea>
        </div>
    </div>

    <div class="product-pricing-row" data-hide-for="instagram_post">
        <div class="form-group" data-hide-for="home_hero" data-hero-text>
            <label>Button 1 text</label>
            <input type="text" name="button_text" value="{{ old('button_text', $isEdit ? $banner->button_text : '') }}" placeholder="e.g. Shop now" maxlength="100">
        </div>
        <div class="form-group">
            <label><span data-hide-for="home_hero" data-hero-text>Button 1 link</span><span data-show-for="home_hero" data-hero-pic hidden>Link when the picture is clicked</span></label>
            <input type="text" name="button_link" value="{{ old('button_link', $isEdit ? $banner->button_link : '') }}" placeholder="e.g. /shop or /rudraksha" maxlength="500">
        </div>
    </div>

    <div class="product-pricing-row" style="margin-top:12px;">
        <div class="form-group" data-hide-for="instagram_post home_hero" data-hero-text>
            <label>Button 2 text</label>
            <input type="text" name="button_text_2" value="{{ old('button_text_2', $isEdit ? $banner->button_text_2 : '') }}" placeholder="Optional, e.g. Book a consultation" maxlength="100">
        </div>
        <div class="form-group" data-hide-for="instagram_post home_hero" data-hero-text>
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
    <h3 class="section-title">Images — desktop &amp; mobile</h3>
    <p class="hint" id="banner-media-note" style="margin-bottom:14px;">Upload a JPG or PNG image.</p>
    <p class="hint banner-size-guide">
        <strong>Desktop:</strong> wide image, ideally <strong>1920 × 1080 px</strong> (16:9).
        <strong>Mobile:</strong> tall image, ideally <strong>1080 × 1920 px</strong> (9:16) — shown on phones instead of the desktop image.
        If no mobile image is added, phones show the desktop image.
    </p>

    @if($isEdit && $banner->images->count())
        <div class="banner-variants-list">
            @foreach($banner->images as $image)
                <div class="banner-variants banner-existing-item">
                    <div class="banner-variant">
                        <p class="banner-variant__label">Desktop {{ $image->is_video ? 'video' : 'image' }}</p>
                        <div class="banner-variant__preview banner-variant__preview--wide">
                            @if($image->is_video)
                                <video src="{{ asset('storage/'.$image->image) }}" controls muted preload="metadata"></video>
                            @else
                                <img src="{{ asset('storage/'.$image->image) }}" alt="">
                            @endif
                        </div>
                        <label class="banner-variant__file">
                            <span>Replace desktop {{ $image->is_video ? 'video / image' : 'image' }}</span>
                            <input type="file" name="replace_images[{{ $image->id }}]" accept=".png,.jpg,.jpeg,.webp,.mp4,.webm,.mov,image/png,image/jpeg,image/webp,video/mp4,video/webm,video/quicktime" data-preview-into="#desk-prev-{{ $image->id }}">
                        </label>
                        <div class="banner-variant__new" id="desk-prev-{{ $image->id }}"></div>
                    </div>
                    <div class="banner-variant">
                        <p class="banner-variant__label">{{ $image->is_video ? 'Mobile image / video still' : 'Mobile image' }}</p>
                        <div class="banner-variant__preview banner-variant__preview--tall">
                            @if($image->mobile_image)
                                <img src="{{ asset('storage/'.$image->mobile_image) }}" alt="">
                            @else
                                <span class="banner-variant__empty">No mobile image yet — phones show the desktop image</span>
                            @endif
                        </div>
                        <label class="banner-variant__file">
                            <span>{{ $image->mobile_image ? 'Replace mobile image' : 'Add mobile image' }}</span>
                            <input type="file" name="replace_mobile[{{ $image->id }}]" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp" data-preview-into="#mob-prev-{{ $image->id }}">
                        </label>
                        <div class="banner-variant__new" id="mob-prev-{{ $image->id }}"></div>
                        @if($image->mobile_image)
                            <label class="remove-check"><input type="checkbox" name="remove_mobile[]" value="{{ $image->id }}"> Remove mobile image</label>
                        @endif
                    </div>
                    <div class="banner-variant banner-variant--meta">
                        <div class="form-group" data-hide-for="home_hero">
                            <label>Order</label>
                            <input type="number" name="existing_sort[{{ $image->id }}]" min="0" value="{{ old('existing_sort.'.$image->id, $image->sort_order) }}">
                        </div>
                        <label class="remove-check">
                            <input type="checkbox" name="remove_images[]" value="{{ $image->id }}"> Delete this picture (desktop &amp; mobile)
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="banner-variants banner-variants--new" style="margin-top:14px;" @if($isEdit && $banner->images->count()) data-optional-new data-hide-for="home_hero" @endif>
        <div class="banner-variant">
            <p class="banner-variant__label">{{ ($isEdit && $banner->images->count()) ? 'Add another desktop image' : 'Desktop image' }} <span class="banner-variant__req">{{ ($isEdit && $banner->images->count()) ? '(optional)' : '' }}</span></p>
            <input type="file" name="images[]" id="banner-images" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
            <div id="banner-image-preview" class="banner-variant__new"></div>
        </div>
        <div class="banner-variant">
            <p class="banner-variant__label" id="banner-mobile-label">Mobile image <span class="banner-variant__req">(optional)</span></p>
            <input type="file" name="mobile_images[0]" id="banner-mobile-image" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
            <div id="banner-mobile-preview" class="banner-variant__new"></div>
        </div>
    </div>
</div>

<div class="offer-form-actions">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ \App\Support\BannerSections::listUrl($selectedSection) }}" class="btn btn-light">Cancel</a>
</div>
