@extends('admin.layouts.app')

@section('title', 'Add User - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ route('admin.users.index') }}" class="back-link">← Back</a>
        <h2>Add User</h2>
    </div>
</div>

<form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" id="user-form" novalidate data-password-required="1">
    @csrf
    @include('admin.users._form')
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/user-form.js') }}"></script>
@endpush
