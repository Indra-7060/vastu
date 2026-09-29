@php
    $isEdit = (bool) $coupon;
    $oldPercent = old('discount_percent', $isEdit && $coupon->discount_type === 'percent' ? $coupon->discount_percent : '');
    $oldAmount = old('discount_amount', $isEdit && $coupon->discount_type === 'amount' ? $coupon->discount_amount : '');
    $maxDiscountStatus = (string) old('max_discount_status', ($coupon->max_discount_status ?? true) ? '1' : '0');
    $minCartStatus = (string) old('min_cart_status', ($coupon->min_cart_status ?? true) ? '1' : '0');
    $maxUsePerUser = (string) old('max_use_per_user', ($coupon->max_use_per_user ?? false) ? '1' : '0');
    $newCustomersOnly = (string) old('new_customers_only', ($coupon->new_customers_only ?? false) ? '1' : '0');
    $membersOnly = (string) old('members_only', ($coupon->members_only ?? false) ? '1' : '0');
    $freeShipping = (string) old('free_shipping', ($coupon->free_shipping ?? false) ? '1' : '0');
    $isPublic = (string) old('is_public', ($coupon->is_public ?? true) ? '1' : '0');
    $offerType = old('offer_type', $coupon->offer_type ?? 'coupon');
    $appliesTo = old('applies_to', $coupon->applies_to ?? 'all');
    $bogoBuyQuantity = old('bogo_buy_quantity', $coupon->bogo_buy_quantity ?? 1);
    $bogoGetQuantity = old('bogo_get_quantity', $coupon->bogo_get_quantity ?? 1);
    $selectedCategories = collect(old('category_ids', $coupon->category_ids ?? []))->map(fn ($id) => (int) $id)->all();
    $selectedProducts = collect(old('product_ids', $coupon->product_ids ?? []))->map(fn ($id) => (int) $id)->all();
    $offerTypes = $offerTypes ?? \App\Models\Coupon::OFFER_TYPES;
    $categories = $categories ?? collect();
    $products = $products ?? collect();
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="offer-form coupon-form" id="coupon-form" novalidate data-image-required="{{ $imageRequired ? '1' : '0' }}">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="offer-form-row offer-form-row-top">
        <label class="offer-label">Description <span>:-</span></label>
        <div class="offer-field form-group">
            <textarea name="description" id="coupon-description" rows="10">{{ old('description', $coupon->description ?? '') }}</textarea>
            <p class="hint" style="margin-top:8px;color:#666;">This description is shown in the top header announcement bar (HTML is converted to plain text).</p>
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Coupon Code <span>:-</span></label>
        <div class="offer-field form-group">
            <input type="text" name="code" value="{{ old('code', $coupon->code ?? '') }}" placeholder="e.g. SAVE50" maxlength="50" style="text-transform:uppercase;">
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Publicly Visible <span>:-</span></label>
        <div class="offer-field form-group">
            <select name="is_public">
                <option value="1" @selected($isPublic === '1')>Yes — show on website</option>
                <option value="0" @selected($isPublic === '0')>No — private code only</option>
            </select>
            <p class="hint muted-hint">Private coupons stay active and can be applied manually, but are not advertised on the website.</p>
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Offer Type <span>:-</span></label>
        <div class="offer-field form-group">
            <select name="offer_type" id="coupon-offer-type">
                @foreach($offerTypes as $key => $label)
                    <option value="{{ $key }}" @selected($offerType === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="offer-form-row" id="coupon-bogo-rule-row">
        <label class="offer-label">BOGO Rule <span>:-</span></label>
        <div class="offer-field form-group">
            <div class="d-flex gap-2 align-items-center">
                <label class="mb-0">Buy</label>
                <input type="number" name="bogo_buy_quantity" value="{{ $bogoBuyQuantity }}" min="1" step="1" class="form-control" style="max-width:100px;">
                <label class="mb-0">Get free</label>
                <input type="number" name="bogo_get_quantity" value="{{ $bogoGetQuantity }}" min="1" step="1" class="form-control" style="max-width:100px;">
            </div>
            <p class="hint muted-hint">Example: Buy 1, Get 1 makes every second matching eligible item free.</p>
        </div>
    </div>

    <div class="coupon-discount-row" id="coupon-discount-row">
        <div class="coupon-discount-col">
            <div class="offer-label-wrap">
                <label class="offer-label">Discount in % <span>:-</span></label>
                <p class="hint muted-hint">(You no need to add % in field)</p>
            </div>
            <div class="offer-field form-group">
                <div class="discount-input">
                    <span class="discount-prefix">%</span>
                    <input type="number" name="discount_percent" id="discount_percent" value="{{ $oldPercent }}" min="0.01" max="100" step="0.01" placeholder="e.g. 50" inputmode="decimal">
                </div>
            </div>
        </div>

        <div class="coupon-or">OR</div>

        <div class="coupon-discount-col">
            <label class="offer-label">Discount in Amount <span>:-</span></label>
            <div class="offer-field form-group">
                <div class="discount-input">
                    <span class="discount-prefix">₹</span>
                    <input type="number" name="discount_amount" id="discount_amount" value="{{ $oldAmount }}" min="0.01" step="0.01" placeholder="e.g. 10" inputmode="decimal">
                </div>
            </div>
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Maximum Discount Status<span>:-</span></label>
        <div class="offer-field form-group">
            <select name="max_discount_status" id="max_discount_status">
                <option value="1" @selected($maxDiscountStatus === '1')>True</option>
                <option value="0" @selected($maxDiscountStatus === '0')>False</option>
            </select>
        </div>
    </div>

    <div class="offer-form-row" id="max-discount-amount-row">
        <label class="offer-label">Maximum Discount (Amount) <span>:-</span></label>
        <div class="offer-field form-group">
            <input type="number" name="max_discount_amount" value="{{ old('max_discount_amount', $coupon->max_discount_amount ?? '') }}" min="0.01" step="0.01" placeholder="e.g. 200" inputmode="decimal">
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Minimum Amount in Cart Status<span>:-</span></label>
        <div class="offer-field form-group">
            <select name="min_cart_status" id="min_cart_status">
                <option value="1" @selected($minCartStatus === '1')>True</option>
                <option value="0" @selected($minCartStatus === '0')>False</option>
            </select>
        </div>
    </div>

    <div class="offer-form-row" id="min-cart-amount-row">
        <label class="offer-label">Minimum Amount in Cart<span>:-</span></label>
        <div class="offer-field form-group">
            <input type="number" name="min_cart_amount" value="{{ old('min_cart_amount', $coupon->min_cart_amount ?? '') }}" min="0.01" step="0.01" placeholder="e.g. 500" inputmode="decimal">
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Applies To <span>:-</span></label>
        <div class="offer-field form-group">
            <select name="applies_to" id="coupon-applies-to">
                <option value="all" @selected($appliesTo === 'all')>Entire cart</option>
                <option value="categories" @selected($appliesTo === 'categories')>Selected categories</option>
                <option value="products" @selected($appliesTo === 'products')>Selected products</option>
            </select>
        </div>
    </div>

    <div class="offer-form-row" id="coupon-categories-row">
        <label class="offer-label">Categories <span>:-</span></label>
        <div class="offer-field form-group">
            <select name="category_ids[]" multiple size="6">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(in_array((int) $category->id, $selectedCategories, true))>{{ $category->title }}</option>
                @endforeach
            </select>
            <p class="hint muted-hint">Hold Ctrl/Cmd to select multiple.</p>
        </div>
    </div>

    <div class="offer-form-row" id="coupon-products-row">
        <label class="offer-label">Products <span>:-</span></label>
        <div class="offer-field form-group">
            <select name="product_ids[]" multiple size="8">
                @foreach($products as $product)
                    <option value="{{ $product->id }}" @selected(in_array((int) $product->id, $selectedProducts, true))>{{ $product->title }}</option>
                @endforeach
            </select>
            <p class="hint muted-hint">Hold Ctrl/Cmd to select multiple.</p>
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Minimum Quantity <span>:-</span></label>
        <div class="offer-field form-group">
            <input type="number" name="min_quantity" value="{{ old('min_quantity', $coupon->min_quantity ?? '') }}" min="1" step="1" placeholder="Optional">
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">New Customers Only<span>:-</span></label>
        <div class="offer-field form-group">
            <select name="new_customers_only">
                <option value="1" @selected($newCustomersOnly === '1')>True</option>
                <option value="0" @selected($newCustomersOnly === '0')>False</option>
            </select>
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Members Only<span>:-</span></label>
        <div class="offer-field form-group">
            <select name="members_only">
                <option value="1" @selected($membersOnly === '1')>True</option>
                <option value="0" @selected($membersOnly === '0')>False</option>
            </select>
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Free Shipping<span>:-</span></label>
        <div class="offer-field form-group">
            <select name="free_shipping">
                <option value="1" @selected($freeShipping === '1')>True</option>
                <option value="0" @selected($freeShipping === '0')>False</option>
            </select>
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Starts At <span>:-</span></label>
        <div class="offer-field form-group">
            <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($coupon->starts_at ?? null)->format('Y-m-d\\TH:i')) }}">
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Ends At <span>:-</span></label>
        <div class="offer-field form-group">
            <input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($coupon->ends_at ?? null)->format('Y-m-d\\TH:i')) }}">
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Total Usage Limit <span>:-</span></label>
        <div class="offer-field form-group">
            <input type="number" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}" min="1" step="1" placeholder="Leave blank for unlimited">
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Maximum Coupon Use Per User<span>:-</span></label>
        <div class="offer-field form-group">
            <select name="max_use_per_user">
                <option value="1" @selected($maxUsePerUser === '1')>True</option>
                <option value="0" @selected($maxUsePerUser === '0')>False</option>
            </select>
        </div>
    </div>

    <div class="offer-form-row offer-form-row-top">
        <div class="offer-label-wrap">
            <label class="offer-label">Select Image <span>:-</span></label>
            <p class="hint">(Recommended resolution: 300x130,400x173)</p>
            <p class="hint">(Accept png, jpg, jpeg, PNG, JPG, JPEG image files)</p>
            <p class="hint">(Recommended jpg, jpeg, JPG, JPEG image files for best compression ratio)</p>
        </div>
        <div class="offer-field form-group">
            <div class="offer-image-box coupon-image-box">
                <input type="file" name="image" id="coupon-image-input" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
                <div class="offer-image-preview coupon-image-preview" id="coupon-image-preview">
                    @if($isEdit && $coupon->image)
                        <img src="{{ asset('storage/'.$coupon->image) }}" alt="{{ $coupon->code }}">
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
