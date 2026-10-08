@extends('admin.layouts.app')

@section('title', 'Add Review - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ route('admin.reviews.index') }}" class="back-link">← Customer Reviews</a>
        <h2>Add Review</h2>
    </div>
</div>

<form method="POST" action="{{ route('admin.reviews.store') }}" enctype="multipart/form-data" novalidate>
    @csrf

    @include('admin.reviews._form')
</form>
@endsection
