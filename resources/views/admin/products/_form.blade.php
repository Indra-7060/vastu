@php
    $product = $product ?? null;
    $hasProduct = $product !== null;
    $isEdit = $hasProduct && $product->exists;
    $selectedColors = old('color_ids', $hasProduct ? $product->colors->pluck('id')->all() : []);
    $selectedSizes = old('size_ids', $hasProduct ? $product->sizes->pluck('id')->all() : []);
    $colorQuantities = old('color_quantities', $hasProduct ? $product->colors->mapWithKeys(fn ($color) => [$color->id => $color->pivot->quantity ?? 0])->all() : []);
    $sizeQuantities = old('size_quantities', $hasProduct ? $product->sizes->mapWithKeys(fn ($size) => [$size->id => $size->pivot->quantity ?? 0])->all() : []);
    $selectedCategory = old('category_id', $hasProduct ? $product->category_id : '');
    $selectedSub = old('sub_category_id', $hasProduct ? $product->sub_category_id : '');
    $hasOld = session()->hasOldInput();
    $flag = function (string $key, bool $default) use ($hasOld, $hasProduct, $product) {
        if ($hasOld) {
            return (bool) old($key);
        }

        return $hasProduct ? (bool) $product->{$key} : $default;
    };

    $highlightRows = old('highlights');
    if (! is_array($highlightRows)) {
        $highlightRows = $hasProduct ? ($product->highlights_items ?? []) : [];
    }
    $highlightRows = array_values(array_map(static fn ($row) => (array) $row, $highlightRows));
    if (count($highlightRows) === 0) {
        $highlightRows = [['title' => '', 'subtitle' => '', 'description' => '', 'icon' => '']];
    }

    $informationRows = old('information');
    if (! is_array($informationRows)) {
        $informationRows = $hasProduct ? ($product->information_items ?? []) : [];
    }
    $informationRows = array_values(array_map(static fn ($row) => (array) $row, $informationRows));
    if (count($informationRows) === 0) {
        $informationRows = [['title' => '', 'text' => '']];
    }

    $specRows = old('specifications');
    if (! is_array($specRows)) {
        $specRows = $hasProduct ? ($product->specifications ?? []) : [];
    }
    $specRows = array_values(array_map(static fn ($row) => (array) $row, $specRows));
    if (count($specRows) === 0) {
        $specRows = [['key' => '', 'value' => '']];
    }


    $productImages = ($isEdit && $product) ? ($product->images ?? collect()) : collect();
    $sharedGallery = $productImages->whereNull('color_id')->values();
    $showBrands = false;
@endphp

<div class="card product-form-card">
    <h3 class="section-title">Basic Details</h3>

    <div class="offer-form-row">
        <label class="offer-label">Title <span>:-</span></label>
        <div class="offer-field form-group">
            <input type="text" name="title" id="product-title" value="{{ old('title', $hasProduct ? $product->title : '') }}" placeholder="Enter title" autocomplete="off">
            <div class="field-error" id="title-dup-error" aria-live="polite"></div>
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Category <span>:-</span></label>
        <div class="offer-field product-select-row">
            <div class="form-group">
                <select name="category_id" id="product-category">
                    <option value="">--Select Category--</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" data-slug="{{ $category->slug }}" @selected($selectedCategory == $category->id)>{{ $category->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <select name="sub_category_id" id="product-subcategory">
                    <option value="">--Select Sub-Category--</option>
                    @foreach($subCategories as $sub)
                        <option value="{{ $sub->id }}" data-category="{{ $sub->category_id }}" @selected($selectedSub == $sub->id)>{{ $sub->title }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Brands section is currently hidden. Flip showBrands to true above to display it. --}}
            @if($showBrands)
            <div class="form-group">
                <select name="brand_id">
                    <option value="">--Select Brand--</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" @selected(old('brand_id', $hasProduct ? $product->brand_id : '') == $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Material &amp; Badge <span>:-</span></label>
        <div class="offer-field product-select-row">
            <div class="form-group">
                <input type="text" name="material" value="{{ old('material', $hasProduct ? $product->material : '') }}" placeholder="e.g. Brass, Copper, Rudraksha Bead, Rose Quartz" maxlength="80">
            </div>
            <div class="form-group">
                <select name="badge">
                    <option value="">--No Badge--</option>
                    @foreach(['New', 'Bestseller', 'Energised', 'Limited Edition'] as $badgeOption)
                        <option value="{{ $badgeOption }}" @selected(old('badge', $hasProduct ? $product->badge : '') === $badgeOption)>{{ $badgeOption }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="offer-form-row offer-form-row-top">
        <label class="offer-label">Description <span>:-</span></label>
        <div class="offer-field form-group">
            <p class="hint" style="margin:0 0 8px;">Shown in the “Description” section of the product page (the first sentences also appear under the price). Press Enter for a new paragraph. Leave empty to hide the description.</p>
            <textarea name="short_description" rows="7" placeholder="Describe the product, its meaning and benefits">{{ old('short_description', $hasProduct ? $product->short_description : '') }}</textarea>
        </div>
    </div>

    <div class="offer-form-row offer-form-row-top">
        <label class="offer-label">Features <span>:-</span></label>
        <div class="offer-field form-group">
            <p class="hint" style="margin:0 0 8px;">One feature per line — shown as a bullet list under the description. Leave empty to hide features for this product.</p>
            <textarea name="features" id="product-features" rows="8" placeholder="Energised before dispatch&#10;Natural, lab-certified material">{{ old('features', $hasProduct ? $product->features : '') }}</textarea>
        </div>
    </div>
</div>



<div class="card product-form-card">
    <h3 class="section-title">Product Pricing</h3>
    <div class="product-pricing-row">
        <div class="form-group">
            <label>M.R.P <span>:-</span></label>
            <input type="number" name="mrp" step="0.01" min="0" value="{{ old('mrp', $hasProduct ? $product->mrp : 0) }}">
        </div>
        <div class="form-group">
            <label>Selling Price <span>:-</span></label>
            <input type="number" name="selling_price" step="0.01" min="0" value="{{ old('selling_price', $hasProduct && (float) $product->selling_price > 0 ? $product->selling_price : '') }}">
        </div>
        <div class="form-group">
            <label>Select Offer <span>:-</span></label>
            <select name="offer_id">
                <option value="">--Select Offer--</option>
                @foreach($offers as $offer)
                    <option value="{{ $offer->id }}" @selected(old('offer_id', $hasProduct ? $product->offer_id : '') == $offer->id)>
                        {{ $offer->title }} ({{ $offer->discount_percent }}%)
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="product-pricing-row" style="margin-top:12px;">
        <div class="form-group">
            <label>Max Unit Buy <span>:-</span></label>
            <input type="number" name="max_unit_buy" min="1" value="{{ old('max_unit_buy', $hasProduct ? $product->max_unit_buy : 10) }}">
        </div>
        <div class="form-group">
            <label>Delivery Charge <span>:-</span></label>
            <input type="number" name="delivery_charge" step="0.01" min="0" value="{{ old('delivery_charge', $hasProduct ? $product->delivery_charge : 0) }}">
        </div>
    </div>
</div>

<div class="card product-form-card">
    <h3 class="section-title">Display Options</h3>
    <p class="hint" style="margin-bottom:12px;">Control where this product appears on the website.</p>
    <div class="product-flags">
        <div class="toggle-row">
            <span>Enable / Active</span>
            <label class="switch">
                <input type="checkbox" name="is_active" value="1" @checked($flag('is_active', true))>
                <span class="slider"></span>
            </label>
        </div>
        <div class="toggle-row">
            <span>Featured Product</span>
            <label class="switch">
                <input type="checkbox" name="is_featured" value="1" @checked($flag('is_featured', false))>
                <span class="slider"></span>
            </label>
        </div>
        <div class="toggle-row">
            <span>New Arrival</span>
            <label class="switch">
                <input type="checkbox" name="is_new_arrival" value="1" @checked($flag('is_new_arrival', false))>
                <span class="slider"></span>
            </label>
        </div>
    </div>
</div>



<div class="card product-form-card">
    <h3 class="section-title">Product Image</h3>
    <p class="hint" style="color:#e53935;">(Recommended resolution: 600x600, 800x800) (Accept png, jpg, jpeg, PNG, JPG, JPEG image files)</p>
    <p class="hint" style="color:#e53935;margin-bottom:14px;">(Recommended jpg, jpeg, JPG, JPEG image files for best compression ratio)</p>

    <div class="product-image-row">
        <div class="form-group">
            <label>Featured Image-1 <span>:-</span></label>
            <div class="offer-image-box">
                <input type="file" name="featured_image" id="featured-image-1" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
                <div class="offer-image-preview" id="preview-featured-1">
                    @if($isEdit && $product->featured_image)
                        <img src="{{ asset('storage/'.$product->featured_image) }}" alt="">
                    @else
                        <span>@include('admin.partials.icon', ['name' => 'image', 'size' => 22])</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Featured Image-2 <span>:-</span></label>
            <div class="offer-image-box">
                <input type="file" name="featured_image_2" id="featured-image-2" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
                <div class="offer-image-preview" id="preview-featured-2">
                    @if($isEdit && $product->featured_image_2)
                        <img src="{{ asset('storage/'.$product->featured_image_2) }}" alt="">
                    @else
                        <span>@include('admin.partials.icon', ['name' => 'image', 'size' => 22])</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="form-group" style="margin-top:14px;">
        <label>Shared Product Gallery <span>:-</span></label>
        <p class="hint">Extra product photos shown in the product page gallery and on listing hover.</p>
        <input type="file" name="gallery[]" id="product-gallery" accept=".png,.jpg,.jpeg,image/png,image/jpeg" multiple>
        @if($sharedGallery->isNotEmpty())
            <div class="gallery-existing">
                @foreach($sharedGallery as $image)
                    <label class="gallery-thumb">
                        <img src="{{ asset('storage/'.$image->image) }}" alt="">
                        <span>Shared · <input type="checkbox" name="remove_gallery[]" value="{{ $image->id }}"> Remove</span>
                    </label>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Image-based "Product Highlights" block is no longer shown on the storefront. --}}
<div class="card product-form-card" hidden>
    <h3 class="section-title">Product Highlights</h3>
    <p class="hint" style="margin-bottom:12px;">Shown on the product details page (image + short text + icon cards).</p>

    <div class="offer-form-row offer-form-row-top">
        <label class="offer-label">Highlights Image <span>:-</span></label>
        <div class="offer-field form-group">
            @if($hasProduct && $product->highlights_image)
                <div style="margin-bottom:8px;">
                    <img src="{{ asset('storage/'.$product->highlights_image) }}" alt="" style="max-height:140px;border-radius:8px;">
                </div>
            @endif
            <input type="file" name="highlights_image" accept=".png,.jpg,.jpeg,.webp,image/*">
        </div>
    </div>

    <div class="offer-form-row offer-form-row-top">
        <label class="offer-label">Short Description <span>:-</span></label>
        <div class="offer-field form-group">
            <textarea name="highlights_short_description" rows="4" placeholder="Short description for Product Highlights">{{ old('highlights_short_description', $hasProduct ? $product->highlights_short_description : '') }}</textarea>
        </div>
    </div>

    <div id="highlights-repeater" class="detail-repeater">
        @foreach($highlightRows as $i => $row)
            <div class="detail-repeater__row" data-repeater-row>
                <div class="detail-repeater__head">
                    <strong>Highlight {{ $i + 1 }}</strong>
                    <button type="button" class="btn btn-light btn-sm" data-remove-row>Remove</button>
                </div>
                <div class="product-pricing-row">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="highlights[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}" placeholder="e.g. FABRIC">
                    </div>
                    <div class="form-group">
                        <label>Sub Title</label>
                        <input type="text" name="highlights[{{ $i }}][subtitle]" value="{{ $row['subtitle'] ?? '' }}" placeholder="e.g. PREMIUM COTTON">
                    </div>
                    <div class="form-group">
                        <label>Icon</label>
                        @if(!empty($row['icon']))
                            <div style="margin-bottom:6px;"><img src="{{ asset('storage/'.$row['icon']) }}" alt="" style="height:36px;"></div>
                            <input type="hidden" name="highlights[{{ $i }}][existing_icon]" value="{{ $row['icon'] }}">
                        @endif
                        <input type="file" name="highlights[{{ $i }}][icon]" accept=".png,.jpg,.jpeg,.webp,.svg,image/*">
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="highlights[{{ $i }}][description]" rows="2" placeholder="Highlight description">{{ $row['description'] ?? '' }}</textarea>
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-light" data-add-row data-target="highlights-repeater" data-template="highlight-template">+ Add Highlight</button>
</div>

<script type="text/template" id="highlight-template">
    <div class="detail-repeater__row" data-repeater-row>
        <div class="detail-repeater__head">
            <strong>Highlight __INDEX_DISPLAY__</strong>
            <button type="button" class="btn btn-light btn-sm" data-remove-row>Remove</button>
        </div>
        <div class="product-pricing-row">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="highlights[__INDEX__][title]" value="" placeholder="e.g. FABRIC">
            </div>
            <div class="form-group">
                <label>Sub Title</label>
                <input type="text" name="highlights[__INDEX__][subtitle]" value="" placeholder="e.g. PREMIUM COTTON">
            </div>
            <div class="form-group">
                <label>Icon</label>
                <input type="file" name="highlights[__INDEX__][icon]" accept=".png,.jpg,.jpeg,.webp,.svg,image/*">
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="highlights[__INDEX__][description]" rows="2" placeholder="Highlight description"></textarea>
        </div>
    </div>
</script>

<div class="card product-form-card">
    <h3 class="section-title">Sections</h3>
    <p class="hint" style="margin-bottom:12px;">Each section appears as its own expandable section on the product page (e.g. “How to use”, “How and where to place”, “Benefits”). Add, edit or remove sections here; empty sections are not shown.</p>
    @php $sharedSections = $hasProduct && $product->category ? collect($product->category->product_sections ?? []) : collect(); @endphp
    @if($sharedSections->isNotEmpty())
        <div class="inherited-sections">
            Also shown on this product — shared by every product in <strong>{{ $product->category->title }}</strong>:
            <ul>@foreach($sharedSections as $shared)<li>{{ $shared['title'] }}</li>@endforeach</ul>
            <span>Edit them once for the whole category in <a href="{{ route('admin.categories.edit', $product->category) }}">Categories → {{ $product->category->title }}</a>. To change one only for this product, add a section here with the same title.</span>
        </div>
    @else
        <p class="hint" style="margin:-4px 0 12px;">Tip: sections needed by every product of a category (e.g. “How to use Rudraksh”) can be written once in Admin → Categories → Edit → “Sections for every product”.</p>
    @endif

    <div id="information-repeater" class="detail-repeater">
        @foreach($informationRows as $i => $row)
            <div class="detail-repeater__row" data-repeater-row>
                <div class="detail-repeater__head">
                    <strong>Section {{ $i + 1 }}</strong>
                    <button type="button" class="btn btn-light btn-sm" data-remove-row>Remove</button>
                </div>
                <div class="product-pricing-row" style="grid-template-columns: 1fr 2fr;">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="information[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}" placeholder="e.g. How to use">
                    </div>
                    <div class="form-group">
                        <label>Text</label>
                        <textarea name="information[{{ $i }}][text]" rows="4" placeholder="Section text">{{ $row['text'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-light" data-add-row data-target="information-repeater" data-template="information-template">+ Add Section</button>
</div>

<script type="text/template" id="information-template">
    <div class="detail-repeater__row" data-repeater-row>
        <div class="detail-repeater__head">
            <strong>Section __INDEX_DISPLAY__</strong>
            <button type="button" class="btn btn-light btn-sm" data-remove-row>Remove</button>
        </div>
        <div class="product-pricing-row" style="grid-template-columns: 1fr 2fr;">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="information[__INDEX__][title]" value="" placeholder="e.g. How to use">
            </div>
            <div class="form-group">
                <label>Text</label>
                <textarea name="information[__INDEX__][text]" rows="4" placeholder="Section text"></textarea>
            </div>
        </div>
    </div>
</script>

<div class="card product-form-card">
    <h3 class="section-title">Product Details</h3>
    <p class="hint" style="margin-bottom:12px;">Label / value rows shown in the “Product details” section (e.g. Material – Brass, Weight – 243 g, Natural Faces – 6). Only rows you add are shown; with no rows the section is hidden.</p>

    <div id="specifications-repeater" class="detail-repeater">
        @foreach($specRows as $i => $row)
            <div class="detail-repeater__row" data-repeater-row>
                <div class="detail-repeater__head">
                    <strong>Spec {{ $i + 1 }}</strong>
                    <button type="button" class="btn btn-light btn-sm" data-remove-row>Remove</button>
                </div>
                <div class="product-pricing-row" style="grid-template-columns: 1fr 1fr auto; align-items:end;">
                    <div class="form-group">
                        <label>Key</label>
                        <input type="text" name="specifications[{{ $i }}][key]" value="{{ $row['key'] ?? '' }}" placeholder="e.g. Origin">
                    </div>
                    <div class="form-group">
                        <label>Value</label>
                        <input type="text" name="specifications[{{ $i }}][value]" value="{{ $row['value'] ?? '' }}" placeholder="e.g. Nepal">
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-light" data-add-row data-target="specifications-repeater" data-template="specifications-template">+ Add Detail</button>
</div>

<script type="text/template" id="specifications-template">
    <div class="detail-repeater__row" data-repeater-row>
        <div class="detail-repeater__head">
            <strong>Spec __INDEX_DISPLAY__</strong>
            <button type="button" class="btn btn-light btn-sm" data-remove-row>Remove</button>
        </div>
        <div class="product-pricing-row" style="grid-template-columns: 1fr 1fr;">
            <div class="form-group">
                <label>Key</label>
                <input type="text" name="specifications[__INDEX__][key]" value="" placeholder="e.g. Origin">
            </div>
            <div class="form-group">
                <label>Value</label>
                <input type="text" name="specifications[__INDEX__][value]" value="" placeholder="e.g. Nepal">
            </div>
        </div>
    </div>
</script>

<div class="card product-form-card">
    <h3 class="section-title">SEO Content</h3>
    <div class="offer-form-row">
        <label class="offer-label">SEO Title <span>:-</span></label>
        <div class="offer-field form-group">
            <input type="text" name="seo_title" value="{{ old('seo_title', $hasProduct ? $product->seo_title : '') }}" placeholder="Enter SEO title">
        </div>
    </div>
    <div class="offer-form-row offer-form-row-top">
        <label class="offer-label">Meta Description <span>:-</span></label>
        <div class="offer-field form-group">
            <textarea name="meta_description" rows="4" placeholder="Enter SEO meta description">{{ old('meta_description', $hasProduct ? $product->meta_description : '') }}</textarea>
        </div>
    </div>
    <div class="offer-form-row">
        <label class="offer-label">Keywords <span>:-</span></label>
        <div class="offer-field form-group">
            <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $hasProduct ? $product->meta_keywords : '') }}" placeholder="Enter SEO keywords">
            <p class="hint" style="color:#e53935;">(Use comma(,) to separate keyword.)</p>
        </div>
    </div>
</div>

<div class="offer-form-actions">
    <button type="submit" class="btn btn-primary">Save</button>
    @if($isEdit)
        <a href="{{ route('admin.products.reviews', $product) }}" class="btn btn-light">Manage Reviews</a>
    @endif
</div>
