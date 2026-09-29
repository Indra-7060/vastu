@extends('admin.layouts.app')

@section('title', 'Dashboard - Vastutathastu')

@section('content')
@php
  $adminName = auth()->user()->name ?? 'Admin';
  $statusTone = fn ($status) => match ($status) {
      'delivered' => 'success',
      'cancelled' => 'danger',
      'shipped' => 'info',
      'packed' => 'warning',
      default => 'neutral',
  };
  $kpis = [
      ['label' => 'Revenue', 'value' => '₹ '.number_format($stats['revenue'], 2), 'meta' => $stats['paid_orders'].' paid '.\Illuminate\Support\Str::plural('order', $stats['paid_orders']), 'icon' => 'rupee', 'route' => 'admin.orders.index'],
      ['label' => 'Orders', 'value' => number_format($stats['orders']), 'meta' => $stats['open_orders'].' awaiting fulfilment', 'icon' => 'orders', 'route' => 'admin.orders.index'],
      ['label' => 'Customers', 'value' => number_format($stats['customers']), 'meta' => $stats['new_customers'].' new in the last 30 days', 'icon' => 'users', 'route' => 'admin.users.index'],
      ['label' => 'Products', 'value' => number_format($stats['products']), 'meta' => $stats['active_products'].' active in store', 'icon' => 'catalog', 'route' => 'admin.products.index'],
  ];
@endphp

<div class="dash">
  <header class="dash-head">
    <div>
      <p class="dash-eyebrow">{{ now()->format('l, j F Y') }}</p>
      <h2 class="dash-title">Welcome back, {{ $adminName }}</h2>
      <p class="dash-sub">Here's what is happening in your store today.</p>
    </div>
    <div class="dash-actions">
      <a class="dash-btn dash-btn--ghost" href="{{ route('admin.orders.index') }}">View orders</a>
      <a class="dash-btn" href="{{ route('admin.products.create') }}">@include('admin.partials.icon', ['name' => 'plus', 'size' => 16]) Add product</a>
    </div>
  </header>

  <section class="dash-kpis" aria-label="Key figures">
    @foreach($kpis as $kpi)
      <a class="dash-kpi" href="{{ route($kpi['route']) }}">
        <span class="dash-kpi__top">
          <span class="dash-kpi__label">{{ $kpi['label'] }}</span>
          <span class="dash-kpi__icon">@include('admin.partials.icon', ['name' => $kpi['icon'], 'size' => 18])</span>
        </span>
        <span class="dash-kpi__value">{{ $kpi['value'] }}</span>
        <span class="dash-kpi__meta">{{ $kpi['meta'] }}</span>
      </a>
    @endforeach
  </section>

  <div class="dash-grid">
    <section class="dash-card dash-card--wide" aria-labelledby="recent-orders-title">
      <header class="dash-card__head">
        <div>
          <h3 id="recent-orders-title">Recent orders</h3>
          <p>The latest orders placed on the storefront.</p>
        </div>
        <a class="dash-link" href="{{ route('admin.orders.index') }}">View all @include('admin.partials.icon', ['name' => 'arrow', 'size' => 14])</a>
      </header>
      @if($recentOrders->isNotEmpty())
        <div class="dash-table-wrap">
          <table class="dash-table">
            <thead>
              <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Date</th>
                <th class="num">Items</th>
                <th>Status</th>
                <th>Payment</th>
                <th class="num">Total</th>
              </tr>
            </thead>
            <tbody>
              @foreach($recentOrders as $order)
                <tr>
                  <td><a class="dash-order" href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                  <td>{{ $order->shipping_name ?: $order->user_name ?: '—' }}</td>
                  <td>{{ optional($order->ordered_at ?? $order->created_at)->format('d M Y') }}</td>
                  <td class="num">{{ $order->items_count }}</td>
                  <td><span class="dash-badge dash-badge--{{ $statusTone($order->status) }}">{{ $order->status_label }}</span></td>
                  <td><span class="dash-badge dash-badge--{{ $order->payment_status === 'paid' ? 'success' : 'neutral' }}">{{ ucfirst($order->payment_status ?? 'pending') }}</span></td>
                  <td class="num">₹ {{ number_format((float) $order->payable_amount, 2) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <p class="dash-empty">No orders yet. New orders will appear here as soon as customers check out.</p>
      @endif
    </section>

    <section class="dash-card" aria-labelledby="catalog-title">
      <header class="dash-card__head">
        <div>
          <h3 id="catalog-title">Catalogue</h3>
          <p>Manage what customers see in the store.</p>
        </div>
      </header>
      <ul class="dash-list">
        @foreach($catalog as $row)
          <li>
            <a href="{{ route($row['route']) }}">
              <span class="dash-list__icon">@include('admin.partials.icon', ['name' => $row['icon'], 'size' => 16])</span>
              <span class="dash-list__label">{{ $row['label'] }}</span>
              <span class="dash-list__count">{{ number_format($row['count']) }}</span>
              @include('admin.partials.icon', ['name' => 'chevron', 'size' => 16, 'class' => 'dash-list__chev'])
            </a>
          </li>
        @endforeach
      </ul>
      <div class="dash-quick">
        <a href="{{ route('admin.categories.create') }}">@include('admin.partials.icon', ['name' => 'plus', 'size' => 14]) New category</a>
        <a href="{{ route('admin.blog-posts.create') }}">@include('admin.partials.icon', ['name' => 'plus', 'size' => 14]) New journal post</a>
      </div>
    </section>
  </div>
</div>
@endsection
