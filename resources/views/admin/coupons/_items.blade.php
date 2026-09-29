@foreach($coupons as $coupon)
    <article class="cat-card">
        <div class="cat-card__media">
            <img src="{{ $coupon->image_url }}" alt="" loading="lazy">
        </div>
        <div class="cat-card__body">
            <div class="cat-card__head">
                <h3 class="cat-card__title cat-card__code" title="{{ $coupon->code }}">{{ $coupon->code }}</h3>
                <span class="dash-badge {{ $coupon->is_active ? 'dash-badge--success' : 'dash-badge--neutral' }}">{{ $coupon->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <p class="cat-card__meta">
                {{ $coupon->discount_label }}
                <span class="dash-badge {{ $coupon->is_public ? 'dash-badge--info' : 'dash-badge--neutral' }}">{{ $coupon->is_public ? 'Public' : 'Private' }}</span>
            </p>
            <div class="cat-card__actions">
                <a href="{{ route('admin.coupons.show', $coupon) }}" class="action-sq" title="View" aria-label="View {{ $coupon->code }}">@include('admin.partials.icon', ['name' => 'eye', 'size' => 16])</a>
                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="action-sq" title="Edit" aria-label="Edit {{ $coupon->code }}">@include('admin.partials.icon', ['name' => 'edit', 'size' => 16])</a>
                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" class="js-delete-form" data-confirm-title="Are you sure?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-sq action-del" title="Delete" aria-label="Delete {{ $coupon->code }}">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
                </form>
                <form method="POST" action="{{ route('admin.coupons.toggle', $coupon) }}" class="cat-card__toggle">
                    @csrf
                    @method('PATCH')
                    <label class="switch" title="Active status">
                        <input type="checkbox" onchange="this.form.submit()" {{ $coupon->is_active ? 'checked' : '' }} aria-label="Active">
                        <span class="slider"></span>
                    </label>
                </form>
            </div>
        </div>
    </article>
@endforeach
