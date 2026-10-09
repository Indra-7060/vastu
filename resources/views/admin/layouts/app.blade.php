<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vastutathastu')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v=vastu-pro-35">
    <link rel="icon" type="image/svg+xml" href="{{ asset('vastu/images/favicon.svg') }}?v=vt2">
    <link rel="icon" type="image/png" sizes="50x50" href="{{ asset('vastu/images/favicon-50.png') }}?v=vt2">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vastu/images/favicon-32.png') }}?v=vt2">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('vastu/images/favicon-16.png') }}?v=vt2">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('vastu/images/apple-touch-icon.png') }}?v=vt2">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}?v=vt2">
    <meta name="theme-color" content="#1c75bc">
</head>
<body class="sidebar-collapsed">
<div class="sidebar-overlay" id="sidebar-overlay"></div>
<div class="admin-wrap">
    <aside class="sidebar" id="admin-sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('vastu/images/logo.svg') }}?v=2" alt="Vastutathastu" class="sidebar-logo">
            <button type="button" class="sidebar-close" id="sidebar-close" aria-label="Close menu">@include('admin.partials.icon', ['name' => 'close', 'size' => 18])</button>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="nav-ico">@include('admin.partials.icon', ['name' => 'dashboard'])</span> Dashboard
            </a>

            <div class="nav-group {{ request()->routeIs('admin.categories.*','admin.sub-categories.*','admin.brands.*','admin.colors.*','admin.sizes.*','admin.offers.*','admin.products.*') ? 'open has-active' : '' }}">
                <button type="button" class="nav-toggle" onclick="this.parentElement.classList.toggle('open')">
                    <span><span class="nav-ico">@include('admin.partials.icon', ['name' => 'catalog'])</span> Products Master</span>
                    <span class="chevron">@include('admin.partials.icon', ['name' => 'chevron', 'size' => 16])</span>
                </button>
                <div class="nav-sub">
                    <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">Categories</a>
                    {{-- Unused sections hidden to keep the panel simple (not removed). Set $showUnused = true to show them again. --}}
                    @php $showUnused = false; @endphp
                    @if($showUnused)
                        <a href="{{ route('admin.sub-categories.index') }}" class="{{ request()->routeIs('admin.sub-categories.*') ? 'active' : '' }}">Sub-Categories</a>
                    @endif
                    {{-- Brands section hidden (not removed). Set $showBrands = true to show again. --}}
                    @php $showBrands = false; @endphp
                    @if($showBrands)
                        <a href="{{ route('admin.brands.index') }}" class="{{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">Brands</a>
                    @endif
                    @if($showUnused)
                        <a href="{{ route('admin.offers.index') }}" class="{{ request()->routeIs('admin.offers.*') ? 'active' : '' }}">Offers</a>
                    @endif
                    <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') && ! request()->routeIs('admin.products.arrange*') ? 'active' : '' }}">Products</a>
                    <a href="{{ route('admin.products.arrange') }}" class="{{ request()->routeIs('admin.products.arrange*') ? 'active' : '' }}">Arrange Products</a>
                </div>
            </div>

            @php
                $navBanner = request()->route('banner');
                $navGallery = request()->routeIs('admin.banners.*') && (request('group') === 'gallery'
                    || \App\Support\BannerSections::isGallery(request('section'))
                    || ($navBanner instanceof \App\Models\Banner && \App\Support\BannerSections::isGallery($navBanner->section)));
            @endphp
            <div class="nav-group {{ (request()->routeIs('admin.banners.*') && ! $navGallery) || request()->routeIs('admin.reviews.*') ? 'open has-active' : '' }}">
                <button type="button" class="nav-toggle" onclick="this.parentElement.classList.toggle('open')">
                    <span><span class="nav-ico">@include('admin.partials.icon', ['name' => 'home'])</span> Home Content</span>
                    <span class="chevron">@include('admin.partials.icon', ['name' => 'chevron', 'size' => 16])</span>
                </button>
                <div class="nav-sub">
                    <a href="{{ route('admin.banners.index') }}" class="{{ request()->routeIs('admin.banners.*') && ! $navGallery ? 'active' : '' }}">Sections & Images</a>
                    <a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">Customer Reviews</a>
                </div>
            </div>

            <div class="nav-group {{ $navGallery ? 'open has-active' : '' }}">
                <button type="button" class="nav-toggle" onclick="this.parentElement.classList.toggle('open')">
                    <span><span class="nav-ico">@include('admin.partials.icon', ['name' => 'image'])</span> Gallery</span>
                    <span class="chevron">@include('admin.partials.icon', ['name' => 'chevron', 'size' => 16])</span>
                </button>
                <div class="nav-sub">
                    <a href="{{ route('admin.banners.index', ['group' => 'gallery']) }}" class="{{ $navGallery ? 'active' : '' }}">Gallery Photos</a>
                </div>
            </div>

            <div class="nav-group {{ request()->routeIs('admin.news-types.*','admin.blog-posts.*') ? 'open has-active' : '' }}">
                <button type="button" class="nav-toggle" onclick="this.parentElement.classList.toggle('open')">
                    <span><span class="nav-ico">@include('admin.partials.icon', ['name' => 'journal'])</span> Journal</span>
                    <span class="chevron">@include('admin.partials.icon', ['name' => 'chevron', 'size' => 16])</span>
                </button>
                <div class="nav-sub">
                    <a href="{{ route('admin.news-types.index') }}" class="{{ request()->routeIs('admin.news-types.*') ? 'active' : '' }}">News Types</a>
                    <a href="{{ route('admin.blog-posts.index') }}" class="{{ request()->routeIs('admin.blog-posts.*') ? 'active' : '' }}">Journal Posts</a>
                </div>
            </div>

            <a href="{{ route('admin.consultations.index') }}" class="nav-link {{ request()->routeIs('admin.consultations.*') ? 'active' : '' }}"><span class="nav-ico">@include('admin.partials.icon', ['name' => 'calendar'])</span> Consultations @if($newConsultations = \App\Models\Consultation::where('status', 'new')->count())<span class="nav-count" title="{{ $newConsultations }} new">{{ $newConsultations }}</span>@endif</a>
            <a href="{{ route('admin.stores.index') }}" class="nav-link {{ request()->routeIs('admin.stores.*') ? 'active' : '' }}"><span class="nav-ico">@include('admin.partials.icon', ['name' => 'pin'])</span> Stores</a>
            <a href="{{ route('admin.coupons.index') }}" class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}"><span class="nav-ico">@include('admin.partials.icon', ['name' => 'coupon'])</span> Coupons</a>
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><span class="nav-ico">@include('admin.partials.icon', ['name' => 'users'])</span> Users</a>
            <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"><span class="nav-ico">@include('admin.partials.icon', ['name' => 'orders'])</span> Orders</a>
            <a href="{{ route('admin.contacts.index') }}" class="nav-link {{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}"><span class="nav-ico">@include('admin.partials.icon', ['name' => 'mail'])</span> Contact List</a>

            <div class="nav-group {{ request()->routeIs('admin.settings.*') ? 'open has-active' : '' }}">
                <button type="button" class="nav-toggle" onclick="this.parentElement.classList.toggle('open')">
                    <span><span class="nav-ico">@include('admin.partials.icon', ['name' => 'settings'])</span> Settings</span>
                    <span class="chevron">@include('admin.partials.icon', ['name' => 'chevron', 'size' => 16])</span>
                </button>
                <div class="nav-sub">
                    <a href="{{ route('admin.settings.pages') }}" class="{{ request()->routeIs('admin.settings.pages*') ? 'active' : '' }}">Web Settings</a>
                    <a href="{{ route('admin.settings.site.edit') }}" class="{{ request()->routeIs('admin.settings.site.*') ? 'active' : '' }}">Site Details</a>
                    <a href="{{ route('admin.settings.shipping.edit') }}" class="{{ request()->routeIs('admin.settings.shipping.*') ? 'active' : '' }}">Shipping Settings</a>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.logout') }}" class="nav-logout-form">
                @csrf
                <button type="submit" class="nav-link nav-logout-btn">
                    <span class="nav-ico">@include('admin.partials.icon', ['name' => 'logout'])</span> Logout
                </button>
            </form>
        </nav>
    </aside>

    <div class="main">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-btn" type="button" id="menu-toggle" aria-label="Toggle menu">@include('admin.partials.icon', ['name' => 'menu', 'size' => 20])</button>
                <h1>Vastutathastu</h1>
            </div>
            <div class="topbar-right">
                <div class="user-menu" id="admin-user-menu">
                    <button type="button" class="user-menu-toggle" id="admin-user-toggle" aria-expanded="false" aria-haspopup="true">
                        <span class="avatar">
                            @if(auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}">
                            @else
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            @endif
                        </span>
                        <span class="user-name">{{ auth()->user()->name }}</span>
                        <span class="user-caret">@include('admin.partials.icon', ['name' => 'chevron', 'size' => 14])</span>
                    </button>
                    <div class="user-dropdown" id="admin-user-dropdown" hidden>
                        <a href="{{ route('admin.profile.edit') }}" class="user-dropdown-item">Profile</a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="user-dropdown-item user-dropdown-logout">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="content">
            @yield('content')
        </main>
    </div>
</div>

{{-- Global delete confirmation modal --}}
<div class="confirm-overlay" id="delete-confirm-overlay" aria-hidden="true"></div>
<div class="confirm-modal" id="delete-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="delete-confirm-title">
    <h3 id="delete-confirm-title">Are you sure?</h3>
    <div class="confirm-actions">
        <button type="button" class="confirm-btn confirm-btn-cancel" id="delete-confirm-cancel">CANCEL</button>
        <button type="button" class="confirm-btn confirm-btn-proceed" id="delete-confirm-proceed">PROCEED</button>
    </div>
</div>

<script src="{{ asset('js/form-validation.js') }}"></script>
<script src="{{ asset('js/admin-forms.js') }}"></script>
<script src="{{ asset('js/inline-edit.js') }}"></script>
<script src="{{ asset('js/delete-confirm.js') }}"></script>
<script src="{{ asset('js/toast.js') }}"></script>
<script>
@if(session('success'))
    Toast.success(@json(session('success')));
@endif
@if(session('error'))
    Toast.error(@json(session('error')));
@endif
@if($errors->any())
    Toast.error(@json($errors->first()));
@endif
</script>
<script>
(function () {
    const body = document.body;
    const overlay = document.getElementById('sidebar-overlay');
    const openBtn = document.getElementById('menu-toggle');
    const closeBtn = document.getElementById('sidebar-close');

    function isMobile() {
        return window.matchMedia('(max-width: 991px)').matches;
    }

    function openSidebar() {
        body.classList.remove('sidebar-collapsed');
        body.classList.add('sidebar-open');
    }

    function closeSidebar() {
        body.classList.add('sidebar-collapsed');
        body.classList.remove('sidebar-open');
    }

    function syncSidebar() {
        if (isMobile()) {
            closeSidebar();
        } else {
            body.classList.remove('sidebar-collapsed', 'sidebar-open');
        }
    }

    openBtn && openBtn.addEventListener('click', function () {
        if (isMobile()) {
            if (body.classList.contains('sidebar-open')) closeSidebar();
            else openSidebar();
        }
    });
    closeBtn && closeBtn.addEventListener('click', closeSidebar);
    overlay && overlay.addEventListener('click', closeSidebar);
    window.addEventListener('resize', syncSidebar);
    syncSidebar();

    document.querySelectorAll('.coming-soon').forEach(el => {
        el.addEventListener('click', e => {
            e.preventDefault();
            alert('This module will be added next.');
        });
    });

    const userMenu = document.getElementById('admin-user-menu');
    const userToggle = document.getElementById('admin-user-toggle');
    const userDropdown = document.getElementById('admin-user-dropdown');
    if (userMenu && userToggle && userDropdown) {
        userToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            const open = userDropdown.hidden;
            userDropdown.hidden = !open;
            userToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            userMenu.classList.toggle('is-open', open);
        });
        document.addEventListener('click', function () {
            userDropdown.hidden = true;
            userToggle.setAttribute('aria-expanded', 'false');
            userMenu.classList.remove('is-open');
        });
        userDropdown.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }
})();
</script>
<script>
// Phones: list tables turn into stacked cards; each cell gets its column name as a label.
(function () {
    document.querySelectorAll('table.admin-table, table.table, table.dash-table').forEach(function (table) {
        var heads = Array.prototype.map.call(table.querySelectorAll('thead th'), function (th) { return th.textContent.replace(/[^\p{L}\p{N} &\/().'-]/gu, '').replace(/\s+/g, ' ').trim(); });
        if (!heads.length) return;
        table.classList.add('vt-cards');
        table.querySelectorAll('tbody tr').forEach(function (tr) {
            Array.prototype.forEach.call(tr.children, function (td, i) {
                if (td.tagName !== 'TD') return;
                if (td.hasAttribute('colspan')) { td.setAttribute('data-label', ''); return; }
                if (/₹/.test(td.textContent) && td.textContent.trim().length < 20) td.classList.add('vt-nowrap');
                var label = heads[i] || '';
                td.setAttribute('data-label', label);
                if (!label && td.querySelector('input[type="checkbox"]')) td.classList.add('vt-cards__check');
                if (/^(action|actions|update)$/i.test(label) || td.querySelector('.user-row-actions, .row-actions')) td.classList.add('vt-cards__actions');
            });
        });
    });
})();
</script>
@stack('scripts')
</body>
</html>
