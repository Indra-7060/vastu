@extends('admin.layouts.auth')

@section('title', 'Reset Password - Vastutathastu')

@section('content')
<form method="POST" action="{{ route('admin.password.update') }}" class="auth-form" id="admin-reset-form" novalidate>
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="field-wrap">
        <div class="input-group">
            <span class="input-icon">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
            </span>
            <input type="email" name="email" value="{{ old('email', $email) }}" placeholder="Email" autocomplete="email">
        </div>
    </div>

    <div class="field-wrap">
        <div class="input-group">
            <span class="input-icon">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12.65 10C11.83 7.67 9.61 6 7 6c-3.31 0-6 2.69-6 6s2.69 6 6 6c2.61 0 4.83-1.67 5.65-4H17v4h4v-4h2v-4H12.65zM7 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
            </span>
            <input type="password" name="password" placeholder="New Password" autocomplete="new-password">
        </div>
    </div>

    <div class="field-wrap">
        <div class="input-group">
            <span class="input-icon">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12.65 10C11.83 7.67 9.61 6 7 6c-3.31 0-6 2.69-6 6s2.69 6 6 6c2.61 0 4.83-1.67 5.65-4H17v4h4v-4h2v-4H12.65zM7 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
            </span>
            <input type="password" name="password_confirmation" placeholder="Confirm Password" autocomplete="new-password">
        </div>
    </div>

    <div class="auth-links">
        <a href="{{ route('admin.login') }}">Click here to Login</a>
    </div>

    <button type="submit" class="auth-btn">RESET PASSWORD</button>
</form>
@endsection
