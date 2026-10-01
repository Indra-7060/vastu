@extends('admin.layouts.app')

@section('title', 'Order List - Vastutathastu')

@section('content')
<div class="product-panel">
    <div class="product-panel-top">
        <h2>Order List</h2>
        <div class="product-panel-actions">
            <form method="GET" action="{{ route('admin.orders.index') }}" id="module-search-form" class="product-search-form">
                @foreach(request()->only(['status', 'date', 'sort', 'dir', 'per_page']) as $key => $val)
                    @if($val !== null && $val !== '')<input type="hidden" name="{{ $key }}" value="{{ $val }}">@endif
                @endforeach
                <div class="product-search">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search here..." autocomplete="off">
                    <button type="submit" class="product-search-btn" aria-label="Search">@include('admin.partials.icon', ['name' => 'search', 'size' => 16])</button>
                </div>
            </form>
            <form method="GET" action="{{ route('admin.orders.index') }}" class="per-page-form">
                @foreach(request()->only(['search', 'status', 'date', 'sort', 'dir']) as $key => $val)
                    @if($val !== null && $val !== '')<input type="hidden" name="{{ $key }}" value="{{ $val }}">@endif
                @endforeach
                <select name="per_page" onchange="this.form.submit()">
                    @foreach([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" @selected(($perPage ?? 10) == $n)>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <div class="product-panel-bottom">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="product-filters order-filters">
            @foreach(request()->only(['search', 'sort', 'dir', 'per_page']) as $key => $val)
                @if($val !== null && $val !== '')<input type="hidden" name="{{ $key }}" value="{{ $val }}">@endif
            @endforeach
            <div class="filter-group">
                <label>Filter by Status</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="">---All---</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Filter by Date</label>
                <input type="text" name="date" id="order-date-filter" value="{{ request('date') }}" placeholder="DD-MM-YYYY" autocomplete="off">
            </div>
        </form>

        <form method="POST" action="{{ route('admin.orders.bulk') }}" id="order-bulk-form" class="product-bulk">
            @csrf
            <label class="select-all-label">
                <input type="checkbox" id="select-all-orders">
                Select All
            </label>
            <div class="bulk-action-wrap">
                <button type="button" class="btn btn-primary product-action-btn" id="order-bulk-action-btn">Action <span class="caret">▾</span></button>
                <div class="bulk-action-menu" id="order-bulk-action-menu" hidden>
                    <button type="submit" name="action" value="delete" class="bulk-item js-bulk-confirm" data-confirm-title="Action: Delete">Delete</button>
                </div>
            </div>
            <div id="order-bulk-ids"></div>
            {{-- Shown as soon as an order is ticked --}}
            <div class="bulk-bar" id="order-bulk-bar" role="region" aria-live="polite" hidden>
                <span class="bulk-bar__count" id="order-bulk-count">0 orders selected</span>
                <button type="submit" name="action" value="delete" class="bulk-bar__delete js-bulk-confirm" data-confirm-title="Delete selected orders?">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16]) Delete selected</button>
                <button type="button" class="bulk-bar__clear" id="order-bulk-clear">Clear selection</button>
            </div>
        </form>
    </div>
</div>

<div class="card table-wrap">
    <table class="admin-table orders-table">
        <thead>
            <tr>
                <th style="width:40px;"></th>
                <th>@include('admin.partials.sort-link', ['column' => 'order_number', 'label' => 'Order ID'])</th>
                <th>@include('admin.partials.sort-link', ['column' => 'user_name', 'label' => 'User Name'])</th>
                <th>@include('admin.partials.sort-link', ['column' => 'user_phone', 'label' => 'User Phone'])</th>
                <th>@include('admin.partials.sort-link', ['column' => 'ordered_at', 'label' => 'Ordered On'])</th>
                <th>@include('admin.partials.sort-link', ['column' => 'status', 'label' => 'Status'])</th>
                <th>@include('admin.partials.sort-link', ['column' => 'payment_status', 'label' => 'Payment'])</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>
                        <input type="checkbox" class="order-check" value="{{ $order->id }}" form="order-bulk-form">
                    </td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order) }}" class="order-id-link">{{ $order->order_number }}</a>
                    </td>
                    <td>{{ $order->user_name ?: ($order->user->name ?? '—') }}</td>
                    <td>{{ $order->user_phone ?: ($order->user->phone ?? '—') }}</td>
                    <td class="ordered-on-cell">
                        <span>{{ $order->ordered_at?->format('d-m-Y') }}</span>
                        <small>{{ $order->ordered_at?->format('h:i A') }}</small>
                    </td>
                    <td>
                        <span class="status-badge {{ $order->status_badge_class }}">{{ $order->status_label }}</span>
                    </td>
                    <td>
                        <span class="{{ $order->payment_badge_class }}">{{ $order->payment_label }}</span>
                    </td>
                    <td>
                        <div class="user-row-actions">
                            <button type="button" class="action-status-btn js-open-status" data-order-id="{{ $order->id }}" title="Update order status and expected delivery date">@include('admin.partials.icon', ['name' => 'truck', 'size' => 16])<span>Update<span class="vt-hide-md"> status</span></span></button>
                            <a href="{{ route('admin.orders.print', $order) }}" target="_blank" class="action-sq action-print" title="Print">@include('admin.partials.icon', ['name' => 'printer', 'size' => 16])</a>
                            <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" class="js-delete-form" data-confirm-title="Are you sure?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-sq action-del" title="Delete">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty-state" style="border:none;">No orders found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination-wrap users-pagination">
    <div class="pagination-info">
        @if($orders->total())
            Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} entries
        @else
            Showing 0 to 0 of 0 entries
        @endif
    </div>
    {{ $orders->links() }}
</div>

@include('admin.orders._status-modal')
@endsection

@push('scripts')
<script>
window.ORDER_STATUS_URL = @json(url('/admin/orders'));
window.CSRF_TOKEN = @json(csrf_token());
</script>
<script src="{{ asset('js/order-admin.js') }}?v=bulk-1"></script>
@endpush
