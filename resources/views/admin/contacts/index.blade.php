@extends('admin.layouts.app')

@section('title', 'Contact List - Vastutathastu')

@section('content')
<div class="card contact-list-card">
    <div class="contact-list-head">
        <h2>Contact List</h2>
    </div>

    <div class="contact-tabs">
        <a href="{{ route('admin.contacts.index', ['tab' => 'subscribe']) }}"
           class="contact-tab {{ $tab === 'subscribe' ? 'active' : '' }}">
            <span class="contact-tab-ico">@include('admin.partials.icon', ['name' => 'mail', 'size' => 16])</span> Subscribe
        </a>
        <a href="{{ route('admin.contacts.index', ['tab' => 'contact']) }}"
           class="contact-tab {{ $tab === 'contact' ? 'active' : '' }}">
            <span class="contact-tab-ico">@include('admin.partials.icon', ['name' => 'journal', 'size' => 16])</span> Contact Form
        </a>
    </div>

    <div class="contact-tab-body">
        @if($tab === 'subscribe')
            @include('admin.contacts._subscribe')
        @else
            @include('admin.contacts._contact-form')
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/contact-admin.js') }}"></script>
@endpush
