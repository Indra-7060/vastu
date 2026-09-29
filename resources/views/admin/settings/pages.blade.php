@extends('admin.layouts.app')

@section('title', 'Page Settings - Vastutathastu')

@section('content')
<div class="card page-settings-card">
    <div class="page-settings-head">
        <h2>Page Settings</h2>
        <p class="page-settings-sub">Web Settings</p>
    </div>

    <div class="page-settings-picker">
        <label for="page-picker">Page to edit</label>
        <select id="page-picker" onchange="if (this.value) window.location = this.value">
            @foreach($groups as $groupLabel => $pages)
                <optgroup label="{{ $groupLabel }}">
                    @foreach($pages as $key => $label)
                        <option value="{{ route('admin.settings.pages', ['page' => $key]) }}" @selected($slug === $key)>{{ $label }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        <p class="hint">Footer pages (Guidance, About and Policies) show “No information available” until you add content here. Leave the content empty or switch the page off to show that message again.</p>
    </div>

    <form method="POST" action="{{ route('admin.settings.pages.update', $page) }}" class="offer-form page-settings-form" id="page-settings-form">
        @csrf
        @method('PUT')

        <div class="offer-form-row">
            <label class="offer-label">{{ $page->title }} URL <span>:-</span></label>
            <div class="offer-field form-group">
                <input type="text" value="{{ $page->public_url }}" readonly class="readonly-input">
                <a href="{{ $page->public_url }}" target="_blank" rel="noopener" class="hint">Open page ↗</a>
            </div>
        </div>

        <div class="offer-form-row">
            <label class="offer-label">Page Title<span class="req">*</span> <span>:-</span></label>
            <div class="offer-field form-group">
                <input type="text" name="title" value="{{ old('title', $page->title) }}" required maxlength="255">
            </div>
        </div>

        <div class="offer-form-row offer-form-row-top">
            <label class="offer-label">Content <span>:-</span></label>
            <div class="offer-field form-group">
                <textarea name="content" id="page-content" rows="14">{{ old('content', $page->content) }}</textarea>
            </div>
        </div>

        <div class="offer-form-row">
            <label class="offer-label">Page Status <span>:-</span></label>
            <div class="offer-field form-group">
                <label class="switch" title="Page status">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $page->is_active) ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <div class="offer-form-actions">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script src="{{ asset('js/page-settings.js') }}"></script>
@endpush
