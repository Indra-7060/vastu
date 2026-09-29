<div class="contact-toolbar">
    <form method="POST" action="{{ route('admin.contacts.subscribers.bulk') }}" id="subscribe-bulk-form" class="contact-bulk-form js-delete-form" data-confirm-title="Delete selected subscribers?">
        @csrf
        <button type="submit" class="btn btn-danger contact-delete-all" id="subscribe-bulk-btn" disabled>
            @include('admin.partials.icon', ['name' => 'trash', 'size' => 15]) Delete All
        </button>
        <div id="subscribe-bulk-ids"></div>
    </form>

    <div class="contact-toolbar-right">
        <form method="GET" action="{{ route('admin.contacts.index') }}" id="module-search-form" class="product-search-form">
            <input type="hidden" name="tab" value="subscribe">
            @foreach(request()->only(['sort', 'dir', 'per_page']) as $key => $val)
                @if($val !== null && $val !== '')<input type="hidden" name="{{ $key }}" value="{{ $val }}">@endif
            @endforeach
            <div class="product-search">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search here..." autocomplete="off">
                <button type="submit" class="product-search-btn" aria-label="Search">@include('admin.partials.icon', ['name' => 'search', 'size' => 16])</button>
            </div>
        </form>
        <form method="GET" action="{{ route('admin.contacts.index') }}" class="per-page-form">
            <input type="hidden" name="tab" value="subscribe">
            @foreach(request()->only(['search', 'sort', 'dir']) as $key => $val)
                @if($val !== null && $val !== '')<input type="hidden" name="{{ $key }}" value="{{ $val }}">@endif
            @endforeach
            <select name="per_page" onchange="this.form.submit()">
                @foreach([10, 25, 50, 100] as $n)
                    <option value="{{ $n }}" @selected(($perPage ?? 10) == $n)>{{ $n }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

<div class="table-wrap">
    <table class="admin-table contact-table">
        <thead>
            <tr>
                <th style="width:40px;">
                    <input type="checkbox" id="select-all-subscribers" title="Select all">
                </th>
                <th style="width:60px;">#</th>
                <th>@include('admin.partials.sort-link', ['column' => 'email', 'label' => 'Email'])</th>
                <th>@include('admin.partials.sort-link', ['column' => 'created_at', 'label' => 'Date'])</th>
                <th style="width:90px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subscribers as $subscriber)
                <tr>
                    <td>
                        <input type="checkbox" class="contact-check subscribe-check" value="{{ $subscriber->id }}" form="subscribe-bulk-form">
                    </td>
                    <td>{{ $subscribers->firstItem() + $loop->index }}</td>
                    <td>{{ $subscriber->email }}</td>
                    <td>{{ $subscriber->created_at?->format('d-m-Y') }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.contacts.subscribers.destroy', $subscriber) }}" class="js-delete-form" data-confirm-title="Are you sure?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-sq action-del" title="Delete">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty-state" style="border:none;">No subscribers found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($subscribers->total() > 0)
    <div class="pagination-wrap contact-pagination">
        <div class="pagination-info">
            Showing {{ $subscribers->firstItem() }} to {{ $subscribers->lastItem() }} of {{ $subscribers->total() }} entries
        </div>
        {{ $subscribers->links() }}
    </div>
@endif
