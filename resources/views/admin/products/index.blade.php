@extends('admin.layouts.app')

@section('title', 'Products - Vastutathastu')

@section('content')
<div class="product-panel">
    <div class="product-panel-top">
        <h2>Products</h2>
        <div class="product-panel-actions">
            <form method="GET" action="{{ route('admin.products.index') }}" id="module-search-form" class="product-search-form">
                @foreach(request()->only(['category_id', 'brand_id', 'offer_id']) as $key => $val)
                    @if($val)<input type="hidden" name="{{ $key }}" value="{{ $val }}">@endif
                @endforeach
                <div class="product-search">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search here..." autocomplete="off">
                    <button type="submit" class="product-search-btn" aria-label="Search">@include('admin.partials.icon', ['name' => 'search', 'size' => 16])</button>
                </div>
            </form>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary product-add-btn">Add New</a>
        </div>
    </div>

    <div class="product-panel-bottom">
        <form method="GET" action="{{ route('admin.products.index') }}" class="product-filters" id="product-filter-form">
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            <select name="category_id" onchange="this.form.submit()">
                <option value="">---All Category---</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->title }}</option>
                @endforeach
            </select>
            {{-- Brands section hidden (not removed). Set $showBrands = true to show again. --}}
            @php($showBrands = false)
            @if($showBrands)
            <select name="brand_id" onchange="this.form.submit()">
                <option value="">---All Brands---</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>{{ $brand->name }}</option>
                @endforeach
            </select>
            @endif
            <select name="offer_id" onchange="this.form.submit()">
                <option value="">---All Offers---</option>
                @foreach($offers as $offer)
                    <option value="{{ $offer->id }}" @selected(request('offer_id') == $offer->id)>{{ $offer->title }}</option>
                @endforeach
            </select>
        </form>

        <form method="POST" action="{{ route('admin.products.bulk') }}" id="product-bulk-form" class="product-bulk">
            @csrf
            <label class="select-all-label">
                <input type="checkbox" id="select-all-products">
                Select All
            </label>
            <div class="bulk-action-wrap">
                <button type="button" class="btn btn-primary product-action-btn" id="bulk-action-btn">Action <span class="caret">▾</span></button>
                <div class="bulk-action-menu" id="bulk-action-menu" hidden>
                    <button type="submit" name="action" value="enable" class="bulk-item">Enable</button>
                    <button type="submit" name="action" value="disable" class="bulk-item js-bulk-confirm" data-confirm-title="Action: Disable">Disable</button>
                    <button type="submit" name="action" value="delete" class="bulk-item js-bulk-confirm" data-confirm-title="Action: Delete">Delete</button>
                </div>
            </div>
            <div id="bulk-ids-container"></div>
        </form>
    </div>
</div>

<div class="product-grid">
    @include('admin.products._items')
</div>

@if($products->isEmpty())
    <div class="empty-state">No products found. <a href="{{ route('admin.products.create') }}">Add New</a></div>
@endif

<div class="pagination-wrap">
    {{ $products->links() }}
</div>
@endsection

@push('scripts')
<script>
(function () {
    const selectAll = document.getElementById('select-all-products');
    const checkboxes = () => Array.from(document.querySelectorAll('.product-check'));
    const bulkForm = document.getElementById('product-bulk-form');
    const idsContainer = document.getElementById('bulk-ids-container');
    const menuBtn = document.getElementById('bulk-action-btn');
    const menu = document.getElementById('bulk-action-menu');

    function syncSelectAll() {
        const boxes = checkboxes();
        const checked = boxes.filter(c => c.checked);
        if (selectAll) {
            selectAll.checked = boxes.length > 0 && checked.length === boxes.length;
            selectAll.indeterminate = checked.length > 0 && checked.length < boxes.length;
        }
        if (checked.length > 0 && window.Toast) {
            // silent count — toast only on action if needed
        }
    }

    function collectIds() {
        idsContainer.innerHTML = '';
        checkboxes().filter(c => c.checked).forEach(c => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = c.value;
            idsContainer.appendChild(input);
        });
    }

    selectAll && selectAll.addEventListener('change', function () {
        checkboxes().forEach(c => { c.checked = selectAll.checked; });
        syncSelectAll();
        if (selectAll.checked && window.Toast) {
            Toast.success('Total ' + checkboxes().length + ' item checked');
        }
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('product-check')) syncSelectAll();
    });

    menuBtn && menuBtn.addEventListener('click', function (e) {
        e.preventDefault();
        menu.hidden = !menu.hidden;
    });

    document.addEventListener('click', function (e) {
        if (!menu || menu.hidden) return;
        if (!e.target.closest('.bulk-action-wrap')) menu.hidden = true;
    });

    bulkForm && bulkForm.addEventListener('submit', function (e) {
        collectIds();
        const count = idsContainer.querySelectorAll('input').length;
        if (count === 0) {
            e.preventDefault();
            if (window.Toast) Toast.error('Please select at least one product.');
            return;
        }

        const submitter = e.submitter;
        if (submitter && submitter.classList.contains('js-bulk-confirm')) {
            e.preventDefault();
            const title = submitter.getAttribute('data-confirm-title') || 'Action: Confirm';
            const overlay = document.getElementById('delete-confirm-overlay');
            const modal = document.getElementById('delete-confirm-modal');
            const titleEl = document.getElementById('delete-confirm-title');
            const cancel = document.getElementById('delete-confirm-cancel');
            const proceed = document.getElementById('delete-confirm-proceed');
            if (!modal) {
                bulkForm.submit();
                return;
            }
            titleEl.textContent = title;
            let msg = modal.querySelector('.confirm-msg');
            if (!msg) {
                msg = document.createElement('p');
                msg.className = 'confirm-msg';
                titleEl.insertAdjacentElement('afterend', msg);
            }
            msg.textContent = 'Do you really want to perform?';
            overlay.classList.add('is-open');
            modal.classList.add('is-open');
            document.body.classList.add('modal-open');

            function close() {
                overlay.classList.remove('is-open');
                modal.classList.remove('is-open');
                document.body.classList.remove('modal-open');
                proceed.onclick = null;
                cancel.onclick = null;
            }

            cancel.onclick = close;
            proceed.onclick = function () {
                close();
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = submitter.value;
                bulkForm.appendChild(actionInput);
                bulkForm.submit();
            };
        }
        menu.hidden = true;
    });
})();
</script>
@endpush
