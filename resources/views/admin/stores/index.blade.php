@extends('admin.layouts.app')

@section('title', 'Stores - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <h2>Stores</h2>
        <p class="page-sub">Locations shown on the website’s Stores page. Hidden stores stay here but are not shown to customers.</p>
    </div>
    <div class="page-head-actions">
        <form method="GET" action="{{ route('admin.stores.index') }}" id="module-search-form">
            <div class="search-box">
                <span>@include('admin.partials.icon', ['name' => 'search', 'size' => 16])</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, city, phone..." autocomplete="off">
            </div>
        </form>
        <a href="{{ route('admin.stores.create') }}" class="btn btn-primary">@include('admin.partials.icon', ['name' => 'plus', 'size' => 16]) Add Store</a>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Store</th>
                    <th>City</th>
                    <th>Type</th>
                    <th>Phone</th>
                    <th>Hours</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stores as $store)
                <tr>
                    <td>{{ $stores->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="store-cell">
                            @if($store->image_url)
                                <img src="{{ $store->image_url }}" alt="" class="table-thumb">
                            @else
                                <span class="store-cell__ico">@include('admin.partials.icon', ['name' => 'pin', 'size' => 18])</span>
                            @endif
                            <span>
                                <strong>{{ $store->name }}</strong>
                                <small>{{ \Illuminate\Support\Str::limit($store->address, 48) }}</small>
                            </span>
                        </div>
                    </td>
                    <td>{{ $store->city }}@if($store->state)<br><small>{{ $store->state }}</small>@endif</td>
                    <td><span class="dash-badge dash-badge--neutral">{{ $store->type }}</span></td>
                    <td class="nowrap">{{ $store->phone ?: '—' }}</td>
                    <td><small>{{ $store->opening_hours ?: '—' }}</small></td>
                    <td>
                        <form method="POST" action="{{ route('admin.stores.toggle', $store) }}">
                            @csrf
                            @method('PATCH')
                            <label class="switch" title="{{ $store->is_active ? 'Shown on website' : 'Hidden from website' }}">
                                <input type="checkbox" onchange="this.form.submit()" {{ $store->is_active ? 'checked' : '' }} aria-label="Shown on website">
                                <span class="slider"></span>
                            </label>
                        </form>
                    </td>
                    <td class="actions-cell">
                        <a href="{{ route('admin.stores.edit', $store) }}" class="action-sq" title="Edit" aria-label="Edit {{ $store->name }}">@include('admin.partials.icon', ['name' => 'edit', 'size' => 16])</a>
                        <form method="POST" action="{{ route('admin.stores.destroy', $store) }}" class="js-delete-form" data-confirm-title="Delete this store?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-sq action-del" title="Delete" aria-label="Delete {{ $store->name }}">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8"><div class="empty-state">No stores found. <a href="{{ route('admin.stores.create') }}">Add a store</a></div></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination-wrap">
    {{ $stores->links() }}
</div>
@endsection
