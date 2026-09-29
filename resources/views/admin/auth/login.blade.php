@extends('admin.layouts.auth')

@section('title', 'Login - Vastutathastu')

@section('content')
<form method="POST" action="{{ route('admin.login.submit') }}" class="auth-form" id="admin-login-form" novalidate>
    @csrf
    <div class="field-wrap">
        <div class="input-group">
            <span class="input-icon">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
            </span>
            <input type="text" name="username" value="{{ old('username') }}" placeholder="Username" autocomplete="username" autofocus>
        </div>
    </div>

    <div class="field-wrap">
        <div class="input-group">
            <span class="input-icon">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12.65 10C11.83 7.67 9.61 6 7 6c-3.31 0-6 2.69-6 6s2.69 6 6 6c2.61 0 4.83-1.67 5.65-4H17v4h4v-4h2v-4H12.65zM7 14c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
            </span>
            <input type="password" name="password" placeholder="Password" autocomplete="current-password">
        </div>
    </div>

    <div class="auth-links">
        <a href="{{ route('admin.password.request') }}">Forgot Password?</a>
    </div>

    <button type="submit" class="auth-btn">LOGIN</button>
</form>
@endsection
