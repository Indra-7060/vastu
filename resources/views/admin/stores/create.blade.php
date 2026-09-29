@extends('admin.layouts.app')

@section('title', 'Add Store - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ route('admin.stores.index') }}" class="back-link">← Stores</a>
        <h2>Add Store</h2>
    </div>
</div>

<form method="POST" action="{{ route('admin.stores.store') }}" enctype="multipart/form-data" novalidate>
    @csrf
    @include('admin.stores._form')
</form>
@endsection
