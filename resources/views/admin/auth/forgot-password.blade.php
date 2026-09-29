@extends('admin.layouts.auth')

@section('title', 'Forgot Password - Vastutathastu')

@section('content')
<form method="POST" action="{{ route('admin.password.email') }}" class="auth-form" id="admin-forgot-form" novalidate>
    @csrf
    <div class="field-wrap">
        <div class="input-group">
            <span class="input-icon">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
            </span>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" autocomplete="email" autofocus>
        </div>
    </div>

    <div class="auth-links">
        <a href="{{ route('admin.login') }}">Click here to Login</a>
    </div>

    <button type="submit" class="auth-btn">SEND</button>
</form>
@endsection
