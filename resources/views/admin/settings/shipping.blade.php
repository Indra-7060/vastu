@extends('admin.layouts.app')

@section('title', 'Shipping Settings - Vastutathastu')

@section('content')
<div class="card page-settings-card">
    <div class="page-settings-head">
        <h2>Shipping Settings</h2>
        <p class="page-settings-sub">Set the free-delivery minimum and flat delivery charge.</p>
    </div>

    <form method="POST" action="{{ route('admin.settings.shipping.update') }}" class="offer-form page-settings-form">
        @csrf
        @method('PUT')

        <div class="offer-form-row">
            <label class="offer-label" for="free_shipping_threshold">Free Shipping Above (₹)<span class="req">*</span> <span>:-</span></label>
            <div class="offer-field form-group">
                <input id="free_shipping_threshold" name="free_shipping_threshold" type="number" min="0" step="0.01" required
                       value="{{ old('free_shipping_threshold', $settings->free_shipping_threshold) }}">
                @error('free_shipping_threshold')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="offer-form-row">
            <label class="offer-label" for="flat_shipping_rate">Shipping Charge Below Minimum (₹)<span class="req">*</span> <span>:-</span></label>
            <div class="offer-field form-group">
                <input id="flat_shipping_rate" name="flat_shipping_rate" type="number" min="0" step="0.01" required
                       value="{{ old('flat_shipping_rate', $settings->flat_shipping_rate) }}">
                @error('flat_shipping_rate')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <p class="page-settings-sub">Customers receive free shipping when their product subtotal is equal to or greater than this amount.</p>

        <div class="offer-form-actions">
            <button type="submit" class="btn btn-primary">Save Shipping Settings</button>
        </div>
    </form>
</div>
@endsection
