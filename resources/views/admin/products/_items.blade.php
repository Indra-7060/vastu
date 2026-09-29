@foreach($products as $product)
    @php
        $img = $product->featured_image
            ? asset('storage/'.$product->featured_image)
            : asset('vastu/images/placeholder.jpg');
        $discount = $product->discount_percent;
        $price = (float) $product->selling_price > 0 ? (float) $product->selling_price : (float) $product->mrp;
    @endphp
    <article class="cat-card cat-card--product">
        <div class="cat-card__media">
            <img src="{{ $img }}" alt="" loading="lazy">
            <label class="cat-card__check" title="Select">
                <input type="checkbox" class="product-check" value="{{ $product->id }}" form="product-bulk-form" aria-label="Select {{ $product->title }}">
            </label>
            @if($product->is_featured)
                <span class="cat-card__flag">Featured</span>
            @endif
        </div>
        <div class="cat-card__body">
            <p class="cat-card__eyebrow">{{ $product->category->title ?? '—' }}@if($product->subCategory) · {{ $product->subCategory->title }}@endif</p>
            <h3 class="cat-card__title" title="{{ $product->title }}">{{ $product->title }}</h3>
            <p class="cat-card__price">
                ₹{{ number_format($price) }}
                @if((float) $product->mrp > $price)
                    <s>₹{{ number_format((float) $product->mrp) }}</s>
                @endif
                @if($discount > 0)
                    <span class="dash-badge dash-badge--success">{{ $discount }}% off</span>
                @endif
            </p>
            <div class="cat-card__actions">
                <a href="{{ route('admin.products.show', $product) }}" class="action-sq" title="View" aria-label="View {{ $product->title }}">@include('admin.partials.icon', ['name' => 'eye', 'size' => 16])</a>
                <a href="{{ route('admin.products.edit', $product) }}" class="action-sq" title="Edit" aria-label="Edit {{ $product->title }}">@include('admin.partials.icon', ['name' => 'edit', 'size' => 16])</a>
                <form method="POST" action="{{ route('admin.products.featured', $product) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="action-sq action-star {{ $product->is_featured ? 'is-on' : '' }}" title="{{ $product->is_featured ? 'Remove from featured' : 'Mark as featured' }}" aria-pressed="{{ $product->is_featured ? 'true' : 'false' }}">@include('admin.partials.icon', ['name' => 'star', 'size' => 16])</button>
                </form>
                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="js-delete-form" data-confirm-title="Are you sure?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-sq action-del" title="Delete" aria-label="Delete {{ $product->title }}">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
                </form>
                <form method="POST" action="{{ route('admin.products.toggle', $product) }}" class="cat-card__toggle">
                    @csrf
                    @method('PATCH')
                    <label class="switch" title="Enable / Disable">
                        <input type="checkbox" onchange="this.form.submit()" {{ $product->is_active ? 'checked' : '' }} aria-label="Active">
                        <span class="slider"></span>
                    </label>
                </form>
            </div>
        </div>
    </article>
@endforeach
