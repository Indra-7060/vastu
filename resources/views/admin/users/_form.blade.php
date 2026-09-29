@php
    $isEdit = isset($user) && $user->exists;
@endphp

<div class="card product-form-card user-form-card">
    <div class="offer-form-row">
        <label class="offer-label">Name <span>:-</span></label>
        <div class="offer-field form-group">
            <input type="text" name="name" value="{{ old('name', $isEdit ? $user->name : '') }}" placeholder="Enter your name">
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Email <span>:-</span></label>
        <div class="offer-field form-group">
            <input type="email" name="email" value="{{ old('email', $isEdit ? $user->email : '') }}" placeholder="Enter your email">
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Phone number <span>:-</span></label>
        <div class="offer-field form-group">
            <input type="text" name="phone" value="{{ old('phone', $isEdit ? $user->phone : '') }}" placeholder="Enter your phone number">
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Password <span>:-</span></label>
        <div class="offer-field form-group">
            <input type="password" name="password" value="" placeholder="********" autocomplete="new-password">
            @if($isEdit)
                <p class="hint">Leave blank if you do not want to change password.</p>
            @endif
        </div>
    </div>

    <div class="offer-form-row">
        <label class="offer-label">Platform <span>:-</span></label>
        <div class="offer-field form-group">
            <select name="platform">
                @php $platform = old('platform', $isEdit ? $user->platform : 'Web'); @endphp
                <option value="Web" @selected($platform === 'Web')>Web</option>
                <option value="Android" @selected($platform === 'Android')>Android</option>
                <option value="iOS" @selected($platform === 'iOS')>iOS</option>
            </select>
        </div>
    </div>

    <div class="offer-form-row offer-form-row-top">
        <div class="offer-label-wrap">
            <label class="offer-label">Select Image <span>:-</span></label>
            <p class="hint" style="color:#e53935;">(Recommended resolution: 300x300, 400x400)</p>
            <p class="hint" style="color:#e53935;">(Accept png, jpg, jpeg, PNG, JPG, JPEG image files)</p>
        </div>
        <div class="offer-field form-group">
            <div class="offer-image-box">
                <input type="file" name="avatar" id="user-avatar-input" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
                <div class="offer-image-preview user-avatar-preview" id="user-avatar-preview">
                    @if($isEdit && $user->avatar)
                        <img src="{{ asset('storage/'.$user->avatar) }}" alt="">
                    @else
                        <span>@include('admin.partials.icon', ['name' => 'user', 'size' => 22])</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="offer-form-actions">
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-light">Cancel</a>
    </div>
</div>
