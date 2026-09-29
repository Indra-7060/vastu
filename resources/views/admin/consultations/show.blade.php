@extends('admin.layouts.app')

@section('title', 'Consultation - '.$consultation->name.' - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ route('admin.consultations.index') }}" class="back-link">← Consultations</a>
        <h2>{{ $consultation->name }}</h2>
        <p class="page-sub">Received {{ $consultation->created_at?->format('d M Y, h:i A') }} · <span class="dash-badge {{ $consultation->status_badge }}">{{ $consultation->status_label }}</span></p>
    </div>
    <div class="page-head-actions">
        @if($consultation->whatsapp_url)
            <a href="{{ $consultation->whatsapp_url }}" target="_blank" rel="noopener" class="btn btn-light">@include('admin.partials.icon', ['name' => 'whatsapp', 'size' => 16]) WhatsApp</a>
        @endif
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $consultation->phone) }}" class="btn btn-light">@include('admin.partials.icon', ['name' => 'phone', 'size' => 16]) Call</a>
        <a href="mailto:{{ $consultation->email }}?subject={{ rawurlencode('Your Vastutathastu consultation') }}" class="btn btn-primary">@include('admin.partials.icon', ['name' => 'mail', 'size' => 16]) Email</a>
    </div>
</div>

<div class="consult-grid">
    <div class="card consult-card">
        <h3 class="section-title">Request</h3>
        <table class="detail-table">
            <tr><th>Name</th><td>{{ $consultation->name }}</td></tr>
            <tr><th>Phone / WhatsApp</th><td>{{ $consultation->phone }}</td></tr>
            <tr><th>Email</th><td>{{ $consultation->email }}</td></tr>
            <tr><th>Interest</th><td>{{ $consultation->interest }}</td></tr>
            <tr><th>Customer account</th><td>
                @if($consultation->user)
                    <a href="{{ route('admin.users.show', $consultation->user) }}" class="user-name-link">{{ $consultation->user->name }}</a>
                @else
                    Guest
                @endif
            </td></tr>
        </table>
        <h3 class="section-title" style="margin-top:20px;">Message</h3>
        <p class="consult-message">{{ $consultation->message ?: 'No message was added.' }}</p>
    </div>

    <div class="card consult-card">
        <h3 class="section-title">Follow-up</h3>
        <p class="consult-notice">@include('admin.partials.icon', ['name' => 'mail', 'size' => 15]) The customer is notified by email{{ $consultation->user || \App\Models\User::where('email', $consultation->email)->exists() ? ' and in their website account' : '' }} when you change the status, send a message or delete this request.</p>
        <form method="POST" action="{{ route('admin.consultations.update', $consultation) }}" class="form-grid" style="max-width:none;">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    @foreach(\App\Models\Consultation::STATUSES as $key => [$label, $style])
                        <option value="{{ $key }}" @selected($consultation->status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Message to customer <span class="label-optional">(optional)</span></label>
                <textarea name="customer_message" rows="4" maxlength="2000" placeholder="e.g. Your session is booked for Saturday, 4 Oct at 11:00 am. We will call you 10 minutes before.">{{ old('customer_message') }}</textarea>
                <p class="hint">Sent to the customer by email and shown in their account notifications.</p>
            </div>
            <div class="form-group">
                <label>Internal note</label>
                <textarea name="admin_note" rows="6" maxlength="2000" placeholder="e.g. Called on 30 Sep, session booked for Saturday 11 am">{{ old('admin_note', $consultation->admin_note) }}</textarea>
                <p class="hint">Only visible to admins.</p>
            </div>
            <div class="consult-actions">
                <button type="submit" class="btn btn-primary">Save &amp; notify</button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.consultations.destroy', $consultation) }}" class="js-delete-form consult-delete" data-confirm-title="Delete this consultation? The customer will be notified.">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">@include('admin.partials.icon', ['name' => 'trash', 'size' => 15]) Delete request</button>
        </form>
    </div>
</div>
@endsection
