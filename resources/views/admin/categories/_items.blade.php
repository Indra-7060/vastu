@forelse($categories as $category)
    <article class="cat-card">
        <div class="cat-card__media">
            <img src="{{ $category->image ? asset('storage/'.$category->image) : asset('vastu/images/placeholder.jpg') }}" alt="" loading="lazy">
        </div>
        <div class="cat-card__body">
            <div class="cat-card__head">
                <h3 class="cat-card__title" title="{{ $category->title }}">{{ $category->title }}</h3>
                <span class="dash-badge {{ $category->is_active ? 'dash-badge--success' : 'dash-badge--neutral' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <p class="cat-card__meta">
                @if($category->show_on_home)
                    @include('admin.partials.icon', ['name' => 'home', 'size' => 14]) Shown on homepage
                @else
                    Not on homepage
                @endif
            </p>
            <div class="cat-card__actions">
                <a href="{{ route('admin.categories.edit', $category) }}" class="action-sq" title="Edit" aria-label="Edit {{ $category->title }}">@include('admin.partials.icon', ['name' => 'edit', 'size' => 16])</a>
                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="js-delete-form" data-confirm-title="Are you sure?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-sq action-del" title="Delete" aria-label="Delete {{ $category->title }}">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
                </form>
                <form method="POST" action="{{ route('admin.categories.toggle', $category) }}" class="cat-card__toggle">
                    @csrf
                    @method('PATCH')
                    <label class="switch" title="Active status">
                        <input type="checkbox" onchange="this.form.submit()" {{ $category->is_active ? 'checked' : '' }} aria-label="Active">
                        <span class="slider"></span>
                    </label>
                </form>
            </div>
        </div>
    </article>
@empty
@endforelse
