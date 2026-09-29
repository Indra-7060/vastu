@extends('admin.layouts.app')

@section('title', 'My Profile - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="back-link">← Back</a>
        <h2>My Profile</h2>
    </div>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="offer-form" id="profile-form" novalidate>
        @csrf
        @method('PUT')

        <div class="offer-form-row offer-form-row-top">
            <label class="offer-label">Avatar <span>:-</span></label>
            <div class="offer-field form-group">
                <div class="offer-image-box">
                    <input type="file" name="avatar" id="profile-avatar-input" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
                    <div class="offer-image-preview user-avatar-preview" id="profile-avatar-preview">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="offer-form-row">
            <label class="offer-label">Name <span>:-</span></label>
            <div class="offer-field form-group">
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="255">
            </div>
        </div>

        <div class="offer-form-row">
            <label class="offer-label">Username <span>:-</span></label>
            <div class="offer-field form-group">
                <input type="text" name="username" value="{{ old('username', $user->username) }}" required maxlength="100">
            </div>
        </div>

        <div class="offer-form-row">
            <label class="offer-label">Email <span>:-</span></label>
            <div class="offer-field form-group">
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255">
            </div>
        </div>

        <div class="offer-form-row">
            <label class="offer-label">Phone <span>:-</span></label>
            <div class="offer-field form-group">
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="20">
            </div>
        </div>

        <div class="offer-form-row">
            <label class="offer-label">New Password <span>:-</span></label>
            <div class="offer-field form-group">
                <input type="password" name="password" placeholder="Leave blank to keep current" autocomplete="new-password">
            </div>
        </div>

        <div class="offer-form-row">
            <label class="offer-label">Confirm Password <span>:-</span></label>
            <div class="offer-field form-group">
                <input type="password" name="password_confirmation" placeholder="Confirm new password" autocomplete="new-password">
            </div>
        </div>

        <div class="offer-form-actions">
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const input = document.getElementById('profile-avatar-input');
    const preview = document.getElementById('profile-avatar-preview');
    if (!input || !preview) return;
    input.addEventListener('change', function () {
        const file = input.files && input.files[0];
        if (!file) return;
        preview.innerHTML = '<img src="' + URL.createObjectURL(file) + '" alt="Preview">';
    });
})();
</script>
@endpush
