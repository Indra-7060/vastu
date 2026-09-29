@foreach($offers as $offer)
    <article class="cat-card">
        <div class="cat-card__media">
            <img src="{{ $offer->image ? asset('storage/'.$offer->image) : asset('vastu/images/placeholder.jpg') }}" alt="" loading="lazy">
        </div>
        <div class="cat-card__body">
            <div class="cat-card__head">
                <h3 class="cat-card__title" title="{{ $offer->title }}">{{ $offer->title }}</h3>
                <span class="dash-badge {{ $offer->is_active ? 'dash-badge--success' : 'dash-badge--neutral' }}">{{ $offer->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <p class="cat-card__meta">{{ $offer->discount_percent }}% off</p>
            <div class="cat-card__actions">
                <a href="{{ route('admin.offers.edit', $offer) }}" class="action-sq" title="Edit" aria-label="Edit {{ $offer->title }}">@include('admin.partials.icon', ['name' => 'edit', 'size' => 16])</a>
                <form method="POST" action="{{ route('admin.offers.destroy', $offer) }}" class="js-delete-form" data-confirm-title="Are you sure?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-sq action-del" title="Delete" aria-label="Delete {{ $offer->title }}">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
                </form>
                <form method="POST" action="{{ route('admin.offers.toggle', $offer) }}" class="cat-card__toggle">
                    @csrf
                    @method('PATCH')
                    <label class="switch" title="Active status">
                        <input type="checkbox" onchange="this.form.submit()" {{ $offer->is_active ? 'checked' : '' }} aria-label="Active">
                        <span class="slider"></span>
                    </label>
                </form>
            </div>
        </div>
    </article>
@endforeach
