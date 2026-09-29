@php
    $types = \App\Models\Store::TYPES;
    $activeChecked = session()->hasOldInput() ? (bool) old('is_active') : (bool) $store->is_active;
@endphp

<div class="card product-form-card">
    <h3 class="section-title">Store details</h3>
    <div class="store-form-grid">
        <div class="form-group span-2">
            <label>Store name *</label>
            <input type="text" name="name" value="{{ old('name', $store->name) }}" maxlength="255" placeholder="e.g. Vastutathastu Flagship — Nashik" required>
        </div>
        <div class="form-group">
            <label>Store type *</label>
            <select name="type" required>
                @foreach($types as $type)
                    <option value="{{ $type }}" @selected(old('type', $store->type) === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Display order</label>
            <input type="number" name="sort_order" min="0" max="9999" value="{{ old('sort_order', $store->sort_order ?? 0) }}">
            <p class="hint">Lower numbers are shown first.</p>
        </div>
    </div>
</div>

<div class="card product-form-card">
    <h3 class="section-title">Address</h3>
    <div class="store-form-grid">
        <div class="form-group span-2">
            <label>Street address *</label>
            <input type="text" name="address" value="{{ old('address', $store->address) }}" maxlength="255" placeholder="Shop / building, street, area" required>
        </div>
        <div class="form-group">
            <label>City *</label>
            <input type="text" name="city" value="{{ old('city', $store->city) }}" maxlength="100" required>
        </div>
        <div class="form-group">
            <label>State</label>
            <input type="text" name="state" value="{{ old('state', $store->state) }}" maxlength="100">
        </div>
        <div class="form-group">
            <label>Pincode</label>
            <input type="text" name="pincode" value="{{ old('pincode', $store->pincode) }}" maxlength="12" inputmode="numeric">
        </div>
        <div class="form-group">
            <label>Google Maps link</label>
            <input type="url" name="map_url" value="{{ old('map_url', $store->map_url) }}" maxlength="500" placeholder="https://maps.google.com/...">
            <p class="hint">Optional. If empty, “Get directions” searches the address.</p>
        </div>
    </div>
</div>

<div class="card product-form-card">
    <h3 class="section-title">Contact &amp; hours</h3>
    <div class="store-form-grid">
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $store->phone) }}" maxlength="40" placeholder="+91 ...">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $store->email) }}" maxlength="255">
        </div>
        <div class="form-group span-2">
            <label>Opening hours</label>
            <input type="text" name="opening_hours" value="{{ old('opening_hours', $store->opening_hours) }}" maxlength="255" placeholder="e.g. Mon–Sat, 10:00 am – 8:30 pm">
        </div>
        <div class="form-group span-2">
            <label>Services</label>
            <input type="text" name="services" value="{{ old('services', $store->services) }}" maxlength="255" placeholder="Comma separated, e.g. Vastu consultation, Rudraksha testing">
        </div>
    </div>
</div>

<div class="card product-form-card">
    <h3 class="section-title">Photo &amp; visibility</h3>
    <div class="store-form-grid">
        <div class="form-group span-2">
            <label>Store photo</label>
            @if($store->image_url)
                <div class="store-form-photo">
                    <img src="{{ $store->image_url }}" alt="">
                    <label class="remove-check"><input type="checkbox" name="remove_image" value="1"> Remove photo</label>
                </div>
            @endif
            <input type="file" name="image" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
            <p class="hint">Optional. Landscape photo, PNG / JPG / WEBP up to 4 MB.</p>
        </div>
        <div class="toggle-row span-2">
            <span>Show on website</span>
            <label class="switch">
                <input type="checkbox" name="is_active" value="1" @checked($activeChecked)>
                <span class="slider"></span>
            </label>
        </div>
    </div>
</div>

<div class="offer-form-actions">
    <button type="submit" class="btn btn-primary">Save store</button>
    <a href="{{ route('admin.stores.index') }}" class="btn btn-light">Cancel</a>
</div>
