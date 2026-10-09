@extends('admin.layouts.app')

@section('title', 'Arrange Products - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <h2>Arrange Products</h2>
        <p class="page-sub">Set the order of products on each category page. Drag a product (or use ↑ ↓), then <strong>Save order</strong>. You can also show a product from another category on this page.</p>
    </div>
</div>

<div class="card product-form-card">
    <form method="GET" action="{{ route('admin.products.arrange') }}" class="arrange-pick">
        <label for="arrange-category">Category</label>
        <select name="category" id="arrange-category" onchange="this.form.submit()">
            @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected($category && $category->id === $c->id)>{{ $c->title }}</option>
            @endforeach
        </select>
        @if($category)
            <a href="{{ $category->frontendUrl() }}" target="_blank" rel="noopener" class="btn btn-light">View page ↗</a>
        @endif
    </form>
</div>

@if($category)
<div class="card product-form-card">
    <h3 class="section-title">{{ $category->title }} — {{ $products->count() }} products</h3>
    <p class="hint" style="margin:-4px 0 14px;">Top of the list = first on the website. Products marked “from …” belong to another category and are also shown here.</p>

    @if($products->isEmpty())
        <div class="empty-state">No products in this category yet.</div>
    @else
    <form method="POST" action="{{ route('admin.products.arrange.save', $category) }}" id="arrange-form">
        @csrf
        <ol class="arrange-list" data-arrange-list>
            @foreach($products as $p)
                <li class="arrange-item{{ $p->is_active ? '' : ' is-hidden' }}" draggable="true" data-arrange-item>
                    <input type="hidden" name="order[]" value="{{ $p->id }}">
                    <span class="arrange-handle" aria-hidden="true">⋮⋮</span>
                    <span class="arrange-num" data-arrange-num>{{ $loop->iteration }}</span>
                    <img src="{{ $p->tile_image_url }}" alt="" class="arrange-thumb" loading="lazy">
                    <span class="arrange-name">
                        <strong>{{ $p->title }}</strong>
                        @if($p->arrange_extra)<span class="arrange-tag">from {{ optional($p->category)->title }}</span>@endif
                        @unless($p->is_active)<span class="arrange-tag arrange-tag--off">hidden on website</span>@endunless
                    </span>
                    <span class="arrange-btns">
                        <button type="button" class="action-sq" data-arrange-up title="Move up" aria-label="Move {{ $p->title }} up">↑</button>
                        <button type="button" class="action-sq" data-arrange-down title="Move down" aria-label="Move {{ $p->title }} down">↓</button>
                        @if($p->arrange_extra)
                            <button type="submit" form="arrange-remove-{{ $p->id }}" class="action-sq action-del" title="Remove from {{ $category->title }}" aria-label="Remove {{ $p->title }} from {{ $category->title }}">×</button>
                        @endif
                    </span>
                </li>
            @endforeach
        </ol>
        <div class="offer-form-actions arrange-save">
            <button type="submit" class="btn btn-primary">Save order</button>
            <span class="hint" data-arrange-dirty hidden>Order changed — remember to save.</span>
        </div>
    </form>
    @foreach($products->where('arrange_extra', true) as $p)
        <form method="POST" action="{{ route('admin.products.arrange.remove', [$category, $p]) }}" id="arrange-remove-{{ $p->id }}" hidden>@csrf @method('DELETE')</form>
    @endforeach
    @endif
</div>

<div class="card product-form-card">
    <h3 class="section-title">Show a product from another category here</h3>
    <form method="POST" action="{{ route('admin.products.arrange.add', $category) }}" class="arrange-add">
        @csrf
        <select name="product_id" required>
            <option value="">— Choose a product —</option>
            @foreach($others->groupBy(fn ($p) => optional($p->category)->title ?: 'Other') as $group => $list)
                <optgroup label="{{ $group }}">
                    @foreach($list as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach
                </optgroup>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Add to {{ $category->title }}</button>
    </form>
    <p class="hint" style="margin-top:8px;">The product stays in its own category too; it is just also listed on the {{ $category->title }} page. It is added at the end — drag it to its place and save.</p>
</div>
@endif

<script>
(function () {
    var list = document.querySelector('[data-arrange-list]'); if (!list) return;
    var dirty = document.querySelector('[data-arrange-dirty]'), dragged = null;
    function renumber() { list.querySelectorAll('[data-arrange-num]').forEach(function (n, i) { n.textContent = i + 1; }); if (dirty) dirty.hidden = false; }
    list.addEventListener('dragstart', function (e) { dragged = e.target.closest('[data-arrange-item]'); if (!dragged) return; dragged.classList.add('is-dragging'); e.dataTransfer.effectAllowed = 'move'; });
    list.addEventListener('dragend', function () { if (dragged) dragged.classList.remove('is-dragging'); dragged = null; renumber(); });
    list.addEventListener('dragover', function (e) {
        e.preventDefault(); if (!dragged) return;
        var over = e.target.closest('[data-arrange-item]'); if (!over || over === dragged) return;
        var r = over.getBoundingClientRect();
        list.insertBefore(dragged, (e.clientY - r.top) > r.height / 2 ? over.nextSibling : over);
    });
    list.addEventListener('click', function (e) {
        var item = e.target.closest('[data-arrange-item]'); if (!item) return;
        if (e.target.closest('[data-arrange-up]') && item.previousElementSibling) { list.insertBefore(item, item.previousElementSibling); renumber(); }
        if (e.target.closest('[data-arrange-down]') && item.nextElementSibling) { list.insertBefore(item.nextElementSibling, item); renumber(); }
    });
})();
</script>
@endsection
