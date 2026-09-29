<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vastutathastu')</title>
    <link rel="stylesheet" href="{{ asset('css/admin-auth.css') }}?v=vastu-1">
</head>
<body class="auth-body">
    <div class="auth-card">
        <div class="auth-brand">
            <img src="{{ asset('vastu/images/logo.svg') }}?v=2" alt="Vastutathastu" class="auth-brand-logo">
        </div>
        <div class="auth-divider">
            <span></span>
            <i></i><i></i><i></i>
            <span></span>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </div>
    <script src="{{ asset('js/form-validation.js') }}"></script>
    <script src="{{ asset('js/admin-auth.js') }}"></script>
    @stack('scripts')
</body>
</html>
