@extends('admin.layouts.app')

@section('title', 'Consultations - Vastutathastu')

@section('content')
@php $statuses = \App\Models\Consultation::STATUSES; @endphp
<div class="page-head">
    <div>
        <h2>Consultations</h2>
        <p class="page-sub">Requests sent from the website’s “Book a consultation” form. Update the status as you follow up — the customer is notified by email and in their account.</p>
    </div>
    <div class="page-head-actions">
        <form method="GET" action="{{ route('admin.consultations.index') }}" id="module-search-form">
            @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
            <div class="search-box">
                <span>@include('admin.partials.icon', ['name' => 'search', 'size' => 16])</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone, email..." autocomplete="off">
            </div>
        </form>
    </div>
</div>

<nav class="status-tabs" aria-label="Filter by status">
    <a href="{{ route('admin.consultations.index', array_filter(['search' => request('search')])) }}" class="status-tab {{ $status ? '' : 'is-active' }}">All <span>{{ $counts->sum() }}</span></a>
    @foreach($statuses as $key => [$label, $style])
        <a href="{{ route('admin.consultations.index', array_filter(['status' => $key, 'search' => request('search')])) }}" class="status-tab {{ $status === $key ? 'is-active' : '' }}">{{ $label }} <span>{{ $counts[$key] ?? 0 }}</span></a>
    @endforeach
</nav>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Interest</th>
                    <th>Message</th>
                    <th>Received</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($consultations as $item)
                <tr class="{{ $item->status === 'new' ? 'is-unread' : '' }}">
                    <td>{{ $consultations->firstItem() + $loop->index }}</td>
                    <td>
                        <a href="{{ route('admin.consultations.show', $item) }}" class="user-name-link">{{ $item->name }}</a>
                        <br><small><a href="mailto:{{ $item->email }}" class="muted-link">{{ $item->email }}</a></small>
                    </td>
                    <td class="nowrap"><a href="tel:{{ preg_replace('/[^0-9+]/', '', $item->phone) }}" class="muted-link">{{ $item->phone }}</a></td>
                    <td><span class="dash-badge dash-badge--neutral">{{ $item->interest }}</span></td>
                    <td class="contact-message-cell" title="{{ $item->message }}">{{ $item->message ? \Illuminate\Support\Str::limit($item->message, 60) : '—' }}</td>
                    <td class="nowrap">{{ $item->created_at?->format('d-m-Y') }}<br><small>{{ $item->created_at?->format('h:i A') }}</small></td>
                    <td>
                        <form method="POST" action="{{ route('admin.consultations.status', $item) }}">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="status-select status-select--{{ $item->status }}" onchange="this.form.submit()" aria-label="Status for {{ $item->name }}">
                                @foreach($statuses as $key => [$label, $style])
                                    <option value="{{ $key }}" @selected($item->status === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td class="actions-cell">
                        <a href="{{ route('admin.consultations.show', $item) }}" class="action-sq" title="View" aria-label="View {{ $item->name }}">@include('admin.partials.icon', ['name' => 'eye', 'size' => 16])</a>
                        @if($item->whatsapp_url)
                            <a href="{{ $item->whatsapp_url }}" target="_blank" rel="noopener" class="action-sq" title="WhatsApp" aria-label="WhatsApp {{ $item->name }}">@include('admin.partials.icon', ['name' => 'whatsapp', 'size' => 16])</a>
                        @endif
                        <form method="POST" action="{{ route('admin.consultations.destroy', $item) }}" class="js-delete-form" data-confirm-title="Delete this consultation? The customer will be notified.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-sq action-del" title="Delete" aria-label="Delete">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8"><div class="empty-state">No consultation requests {{ $status ? 'with this status' : 'yet' }}.</div></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination-wrap">
    {{ $consultations->links() }}
</div>
@endsection
