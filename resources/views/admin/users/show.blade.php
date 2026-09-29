@extends('admin.layouts.app')

@section('title', $user->name.' - Users - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ route('admin.users.index') }}" class="back-link">← Back</a>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">Edit</a>
    </div>
</div>

<div class="user-profile-banner">
    <div class="user-info-card">
        <div class="user-avatar-wrap lg">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="user-avatar">
            @if($user->is_google_login)
                <span class="google-badge" title="Google login">G</span>
            @endif
        </div>
        <strong>{{ $user->name }}</strong>
    </div>
    <div class="user-info-card">
        <span class="info-ico">@include('admin.partials.icon', ['name' => 'mail', 'size' => 16])</span>
        <span>{{ $user->email }}</span>
    </div>
    <div class="user-info-card">
        <span class="info-ico">@include('admin.partials.icon', ['name' => 'phone', 'size' => 16])</span>
        <span>{{ $user->phone ?: '—' }}</span>
    </div>
    <div class="user-info-card">
        <span>REGISTER ON: {{ $user->created_at?->format('d-m-Y h:i A') }}</span>
    </div>
</div>

<div class="user-tabs-wrap card">
    <div class="user-tabs">
        <a href="{{ route('admin.users.show', [$user, 'tab' => 'wishlist']) }}" class="{{ $tab === 'wishlist' ? 'active' : '' }}">Wishlist</a>
        <a href="{{ route('admin.users.show', [$user, 'tab' => 'cart']) }}" class="{{ $tab === 'cart' ? 'active' : '' }}">My Cart</a>
        <a href="{{ route('admin.users.show', [$user, 'tab' => 'orders']) }}" class="{{ $tab === 'orders' ? 'active' : '' }}">Orders</a>
        <a href="{{ route('admin.users.show', [$user, 'tab' => 'profile']) }}" class="{{ $tab === 'profile' ? 'active' : '' }}">Profile</a>
        <a href="{{ route('admin.users.show', [$user, 'tab' => 'reviews']) }}" class="{{ $tab === 'reviews' ? 'active' : '' }}">Reviews</a>
    </div>

    <div class="user-tab-body">
        @if(in_array($tab, ['wishlist', 'cart', 'orders', 'reviews'], true))
            <div class="user-tab-toolbar">
                <form method="GET" action="{{ route('admin.users.show', $user) }}" class="user-tab-search">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                    @if(request('dir'))<input type="hidden" name="dir" value="{{ request('dir') }}">@endif
                    <div class="product-search">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search here..." autocomplete="off">
                        <button type="submit" class="product-search-btn" aria-label="Search">@include('admin.partials.icon', ['name' => 'search', 'size' => 16])</button>
                    </div>
                </form>
                <form method="GET" action="{{ route('admin.users.show', $user) }}" class="per-page-form">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    @foreach(request()->only(['q', 'sort', 'dir']) as $key => $val)
                        @if($val)<input type="hidden" name="{{ $key }}" value="{{ $val }}">@endif
                    @endforeach
                    <select name="per_page" onchange="this.form.submit()">
                        @foreach([10, 25, 50, 100] as $n)
                            <option value="{{ $n }}" @selected($perPage == $n)>{{ $n }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        @endif

        @if($tab === 'wishlist')
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'product', 'label' => 'Product'])</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'price', 'label' => 'Price'])</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'created_at', 'label' => 'Created On'])</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($wishlist as $item)
                            <tr>
                                <td>{{ $wishlist->firstItem() + $loop->index }}</td>
                                <td>
                                    @if($item->product?->featured_image)
                                        <img src="{{ asset('storage/'.$item->product->featured_image) }}" alt="" class="table-thumb">
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $item->product->title ?? '—' }}</td>
                                <td>{{ $item->product ? number_format($item->product->selling_price, 2) : '—' }}</td>
                                <td>{{ $item->created_at?->format('d-m-Y') }}</td>
                                <td>—</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="empty-state" style="border:none;">No data available in table</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('admin.users._tab-pagination', ['paginator' => $wishlist])

        @elseif($tab === 'cart')
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'product', 'label' => 'Product'])</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'quantity', 'label' => 'Qty'])</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'price', 'label' => 'Price'])</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'total', 'label' => 'Total Price'])</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'created_at', 'label' => 'Created On'])</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cartItems as $item)
                            @php $price = $item->product->selling_price ?? 0; @endphp
                            <tr>
                                <td>{{ $cartItems->firstItem() + $loop->index }}</td>
                                <td>
                                    @if($item->product?->featured_image)
                                        <img src="{{ asset('storage/'.$item->product->featured_image) }}" alt="" class="table-thumb">
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $item->product->title ?? '—' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($price, 2) }}</td>
                                <td>{{ number_format($price * $item->quantity, 2) }}</td>
                                <td>{{ $item->created_at?->format('d-m-Y') }}</td>
                                <td>—</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="empty-state" style="border:none;">No data available in table</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('admin.users._tab-pagination', ['paginator' => $cartItems])

        @elseif($tab === 'orders')
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'order_number', 'label' => 'Order ID'])</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'user_name', 'label' => 'User Name'])</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'user_phone', 'label' => 'User Phone'])</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'payable_amount', 'label' => 'Payable Amount'])</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'ordered_at', 'label' => 'Ordered On'])</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'status', 'label' => 'Status'])</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>{{ $orders->firstItem() + $loop->index }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="order-id-link">{{ $order->order_number }}</a>
                                </td>
                                <td>{{ $order->user_name ?: $user->name }}</td>
                                <td>{{ $order->user_phone ?: ($user->phone ?: '—') }}</td>
                                <td>{{ number_format($order->payable_amount, 2) }}</td>
                                <td>{{ ($order->ordered_at ?: $order->created_at)?->format('d-m-Y') }}</td>
                                <td>
                                    <span class="status-badge {{ $order->status_badge_class }}">{{ $order->status_label }}</span>
                                </td>
                                <td>
                                    <div class="user-row-actions">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="action-sq action-view" title="View">@include('admin.partials.icon', ['name' => 'eye', 'size' => 16])</a>
                                        <a href="{{ route('admin.orders.print', $order) }}" target="_blank" class="action-sq action-print" title="Print">@include('admin.partials.icon', ['name' => 'printer', 'size' => 16])</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="empty-state" style="border:none;">No data available in table</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('admin.users._tab-pagination', ['paginator' => $orders])

        @elseif($tab === 'reviews')
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'product', 'label' => 'Product'])</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'rating', 'label' => 'Ratings'])</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'comment', 'label' => 'Reviews'])</th>
                            <th>Image</th>
                            <th>@include('admin.partials.sort-link', ['column' => 'created_at', 'label' => 'Review On'])</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $review)
                            <tr>
                                <td>{{ $reviews->firstItem() + $loop->index }}</td>
                                <td>{{ $review->product->title ?? '—' }}</td>
                                <td><span class="rating-stars" aria-label="{{ $review->rating }} out of 5">@for($i = 1; $i <= 5; $i++)<span class="{{ $i <= $review->rating ? 'is-on' : '' }}">★</span>@endfor</span></td>
                                <td>{{ \Illuminate\Support\Str::limit($review->comment, 60) }}</td>
                                <td>
                                    @if($review->image)
                                        <img src="{{ asset('storage/'.$review->image) }}" alt="" class="table-thumb">
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $review->created_at?->format('d-m-Y') }}</td>
                                <td>—</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="empty-state" style="border:none;">No data available in table</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('admin.users._tab-pagination', ['paginator' => $reviews])

        @elseif($tab === 'profile')
            <div class="profile-menu-list">
                <div class="profile-menu-block">
                    <h4><span>@include('admin.partials.icon', ['name' => 'pin', 'size' => 16])</span> Manage Address</h4>
                    @forelse($addresses as $address)
                        <div class="profile-item-card">
                            <strong>{{ $address->label ?: 'Address' }}</strong>
                            <p>{{ $address->name }} @if($address->phone) · {{ $address->phone }} @endif</p>
                            <p>{{ $address->address_line1 }}@if($address->address_line2), {{ $address->address_line2 }}@endif</p>
                            <p>{{ collect([$address->city, $address->state, $address->pincode, $address->country])->filter()->implode(', ') }}</p>
                            @if($address->is_default)<span class="tag-default">Default</span>@endif
                        </div>
                    @empty
                        <p class="hint">No addresses saved.</p>
                    @endforelse
                </div>

                <div class="profile-menu-block">
                    <h4><span>@include('admin.partials.icon', ['name' => 'card', 'size' => 16])</span> Saved Bank Account</h4>
                    @forelse($bankAccounts as $bank)
                        <div class="profile-item-card">
                            <strong>{{ $bank->account_holder }}</strong>
                            <p>{{ $bank->bank_name }}</p>
                            <p>A/C: {{ $bank->account_number }} @if($bank->ifsc) · IFSC: {{ $bank->ifsc }} @endif</p>
                            @if($bank->is_default)<span class="tag-default">Default</span>@endif
                        </div>
                    @empty
                        <p class="hint">No bank accounts saved.</p>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
