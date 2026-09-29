@extends('admin.layouts.app')

@section('title', 'Edit User - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ route('admin.users.index') }}" class="back-link">← Back</a>
        <h2>Edit User</h2>
    </div>
</div>

<form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" id="user-form" novalidate data-password-required="0">
    @csrf
    @method('PUT')
    @include('admin.users._form')
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/user-form.js') }}"></script>
@endpush
