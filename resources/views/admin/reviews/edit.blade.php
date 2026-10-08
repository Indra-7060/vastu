@extends('admin.layouts.app')

@section('title', 'Edit Review - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ route('admin.reviews.index') }}" class="back-link">← Customer Reviews</a>
        <h2>Edit Review</h2>
    </div>
</div>

<form method="POST" action="{{ route('admin.reviews.update', $review) }}" enctype="multipart/form-data" novalidate>
    @csrf
    @method('PUT')
    @include('admin.reviews._form')
</form>
@endsection
