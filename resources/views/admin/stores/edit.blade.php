@extends('admin.layouts.app')

@section('title', 'Edit Store - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ route('admin.stores.index') }}" class="back-link">← Stores</a>
        <h2>Edit Store</h2>
    </div>
</div>

<form method="POST" action="{{ route('admin.stores.update', $store) }}" enctype="multipart/form-data" novalidate>
    @csrf
    @method('PUT')
    @include('admin.stores._form')
</form>
@endsection
