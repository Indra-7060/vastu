<div class="contact-toolbar">
    <form method="POST" action="{{ route('admin.contacts.messages.bulk') }}" id="contact-bulk-form" class="contact-bulk-form js-delete-form" data-confirm-title="Delete selected messages?">
        @csrf
        <button type="submit" class="btn btn-danger contact-delete-all" id="contact-bulk-btn" disabled>
            @include('admin.partials.icon', ['name' => 'trash', 'size' => 15]) Delete All
        </button>
        <div id="contact-bulk-ids"></div>
    </form>

    <div class="contact-toolbar-right">
        <form method="GET" action="{{ route('admin.contacts.index') }}" id="module-search-form" class="product-search-form">
            <input type="hidden" name="tab" value="contact">
            @foreach(request()->only(['sort', 'dir', 'per_page']) as $key => $val)
                @if($val !== null && $val !== '')<input type="hidden" name="{{ $key }}" value="{{ $val }}">@endif
            @endforeach
            <div class="product-search">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search here..." autocomplete="off">
                <button type="submit" class="product-search-btn" aria-label="Search">@include('admin.partials.icon', ['name' => 'search', 'size' => 16])</button>
            </div>
        </form>
        <form method="GET" action="{{ route('admin.contacts.index') }}" class="per-page-form">
            <input type="hidden" name="tab" value="contact">
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
                    <input type="checkbox" id="select-all-messages" title="Select all">
                </th>
                <th>@include('admin.partials.sort-link', ['column' => 'name', 'label' => 'Name'])</th>
                <th>@include('admin.partials.sort-link', ['column' => 'email', 'label' => 'Email'])</th>
                <th>Phone</th>
                <th>City</th>
                <th>@include('admin.partials.sort-link', ['column' => 'subject', 'label' => 'Subject'])</th>
                <th>@include('admin.partials.sort-link', ['column' => 'message', 'label' => 'Message'])</th>
                <th>@include('admin.partials.sort-link', ['column' => 'created_at', 'label' => 'Date'])</th>
                <th style="width:90px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($messages as $message)
                <tr>
                    <td>
                        <input type="checkbox" class="contact-check message-check" value="{{ $message->id }}" form="contact-bulk-form">
                    </td>
                    <td>{{ $message->name }}</td>
                    <td>{{ $message->email }}</td>
                    <td class="nowrap">{{ $message->phone ?: '—' }}</td>
                    <td>{{ $message->city ?: '—' }}</td>
                    <td>{{ $message->subject ?: '—' }}</td>
                    <td class="contact-message-cell" title="{{ $message->message }}">{{ \Illuminate\Support\Str::limit($message->message, 80) }}</td>
                    <td>{{ $message->created_at?->format('d-m-Y') }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.contacts.messages.destroy', $message) }}" class="js-delete-form" data-confirm-title="Are you sure?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-sq action-del" title="Delete">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="empty-state" style="border:none;">No contact messages found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($messages->total() > 0)
    <div class="pagination-wrap contact-pagination">
        <div class="pagination-info">
            Showing {{ $messages->firstItem() }} to {{ $messages->lastItem() }} of {{ $messages->total() }} entries
        </div>
        {{ $messages->links() }}
    </div>
@endif
