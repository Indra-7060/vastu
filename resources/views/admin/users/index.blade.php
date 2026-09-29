@extends('admin.layouts.app')

@section('title', 'Users - Vastutathastu')

@section('content')
<div class="product-panel">
    <div class="product-panel-top">
        <h2>Users</h2>
        <div class="product-panel-actions">
            <form method="GET" action="{{ route('admin.users.index') }}" id="module-search-form" class="product-search-form">
                @foreach(request()->only(['sort', 'dir', 'per_page']) as $key => $val)
                    @if($val)<input type="hidden" name="{{ $key }}" value="{{ $val }}">@endif
                @endforeach
                <div class="product-search">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search here..." autocomplete="off">
                    <button type="submit" class="product-search-btn" aria-label="Search">@include('admin.partials.icon', ['name' => 'search', 'size' => 16])</button>
                </div>
            </form>
            <form method="GET" action="{{ route('admin.users.index') }}" class="per-page-form">
                @foreach(request()->only(['search', 'sort', 'dir']) as $key => $val)
                    @if($val)<input type="hidden" name="{{ $key }}" value="{{ $val }}">@endif
                @endforeach
                <select name="per_page" onchange="this.form.submit()">
                    @foreach([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" @selected(($perPage ?? 10) == $n)>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary product-add-btn">Add New</a>
        </div>
    </div>

    <div class="product-panel-bottom" style="justify-content:flex-end;">
        <form method="POST" action="{{ route('admin.users.bulk') }}" id="user-bulk-form" class="product-bulk">
            @csrf
            <label class="select-all-label">
                <input type="checkbox" id="select-all-users">
                Select All
            </label>
            <div class="bulk-action-wrap">
                <button type="button" class="btn btn-primary product-action-btn" id="user-bulk-action-btn">Action <span class="caret">▾</span></button>
                <div class="bulk-action-menu" id="user-bulk-action-menu" hidden>
                    <button type="submit" name="action" value="enable" class="bulk-item">Enable</button>
                    <button type="submit" name="action" value="disable" class="bulk-item js-bulk-confirm" data-confirm-title="Action: Disable">Disable</button>
                    <button type="submit" name="action" value="delete" class="bulk-item js-bulk-confirm" data-confirm-title="Action: Delete">Delete</button>
                </div>
            </div>
            <div id="user-bulk-ids"></div>
        </form>
    </div>
</div>

<div class="card table-wrap">
    <table class="admin-table users-table">
        <thead>
            <tr>
                <th style="width:40px;"></th>
                <th>@include('admin.partials.sort-link', ['column' => 'platform', 'label' => 'Platform'])</th>
                <th>@include('admin.partials.sort-link', ['column' => 'name', 'label' => 'Name'])</th>
                <th>@include('admin.partials.sort-link', ['column' => 'email', 'label' => 'Email'])</th>
                <th>@include('admin.partials.sort-link', ['column' => 'created_at', 'label' => 'Register On'])</th>
                <th>@include('admin.partials.sort-link', ['column' => 'is_active', 'label' => 'Status'])</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>
                        <input type="checkbox" class="user-check" value="{{ $user->id }}" form="user-bulk-form">
                    </td>
                    <td>{{ $user->platform ?: 'Web' }}</td>
                    <td>
                        <div class="user-name-cell">
                            <div class="user-avatar-wrap">
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="user-avatar">
                                @if($user->is_google_login)
                                    <span class="google-badge" title="Google login">G</span>
                                @endif
                            </div>
                            <a href="{{ route('admin.users.show', $user) }}" class="user-name-link">{{ $user->name }}</a>
                        </div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->created_at?->format('d-m-Y') }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                            @csrf
                            @method('PATCH')
                            <label class="switch" title="Enable / Disable">
                                <input type="checkbox" onchange="this.form.submit()" {{ $user->is_active ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </form>
                    </td>
                    <td>
                        <div class="user-row-actions">
                            <a href="{{ route('admin.users.show', $user) }}" class="action-sq action-view" title="View">@include('admin.partials.icon', ['name' => 'eye', 'size' => 16])</a>
                            <a href="{{ route('admin.users.edit', $user) }}" class="action-sq action-edit" title="Edit">@include('admin.partials.icon', ['name' => 'edit', 'size' => 16])</a>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="js-delete-form" data-confirm-title="Are you sure?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-sq action-del" title="Delete">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty-state" style="border:none;">No users found. <a href="{{ route('admin.users.create') }}">Add New</a></td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination-wrap users-pagination">
    <div class="pagination-info">
        @if($users->total())
            Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries
        @else
            Showing 0 to 0 of 0 entries
        @endif
    </div>
    {{ $users->links() }}
</div>
@endsection

@push('scripts')
<script>
(function () {
    const selectAll = document.getElementById('select-all-users');
    const checkboxes = () => Array.from(document.querySelectorAll('.user-check'));
    const bulkForm = document.getElementById('user-bulk-form');
    const idsBox = document.getElementById('user-bulk-ids');
    const menuBtn = document.getElementById('user-bulk-action-btn');
    const menu = document.getElementById('user-bulk-action-menu');

    function syncSelectAll() {
        const boxes = checkboxes();
        const checked = boxes.filter(c => c.checked);
        if (selectAll) {
            selectAll.checked = boxes.length > 0 && checked.length === boxes.length;
            selectAll.indeterminate = checked.length > 0 && checked.length < boxes.length;
        }
    }

    function collectIds() {
        idsBox.innerHTML = '';
        checkboxes().filter(c => c.checked).forEach(c => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = c.value;
            idsBox.appendChild(input);
        });
    }

    selectAll && selectAll.addEventListener('change', function () {
        checkboxes().forEach(c => { c.checked = selectAll.checked; });
        syncSelectAll();
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('user-check')) syncSelectAll();
    });

    menuBtn && menuBtn.addEventListener('click', function (e) {
        e.preventDefault();
        menu.hidden = !menu.hidden;
    });

    document.addEventListener('click', function (e) {
        if (!menu || menu.hidden) return;
        if (!e.target.closest('.bulk-action-wrap')) menu.hidden = true;
    });

    bulkForm && bulkForm.addEventListener('submit', function (e) {
        collectIds();
        if (!idsBox.querySelectorAll('input').length) {
            e.preventDefault();
            if (window.Toast) Toast.error('Please select at least one user.');
            return;
        }

        const submitter = e.submitter;
        if (submitter && submitter.classList.contains('js-bulk-confirm')) {
            e.preventDefault();
            const title = submitter.getAttribute('data-confirm-title') || 'Are you sure?';
            const overlay = document.getElementById('delete-confirm-overlay');
            const modal = document.getElementById('delete-confirm-modal');
            const titleEl = document.getElementById('delete-confirm-title');
            const cancel = document.getElementById('delete-confirm-cancel');
            const proceed = document.getElementById('delete-confirm-proceed');
            if (!modal) { bulkForm.submit(); return; }

            titleEl.textContent = title;
            let msg = modal.querySelector('.confirm-msg');
            if (!msg) {
                msg = document.createElement('p');
                msg.className = 'confirm-msg';
                titleEl.insertAdjacentElement('afterend', msg);
            }
            msg.textContent = 'Do you really want to perform?';
            overlay.classList.add('is-open');
            modal.classList.add('is-open');
            document.body.classList.add('modal-open');

            function close() {
                overlay.classList.remove('is-open');
                modal.classList.remove('is-open');
                document.body.classList.remove('modal-open');
                proceed.onclick = null;
                cancel.onclick = null;
            }
            cancel.onclick = close;
            proceed.onclick = function () {
                close();
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = submitter.value;
                bulkForm.appendChild(actionInput);
                bulkForm.submit();
            };
        }
        menu.hidden = true;
    });
})();
</script>
@endpush
