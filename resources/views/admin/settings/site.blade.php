@extends('admin.layouts.app')

@section('title', 'Site Details - Vastutathastu')

@section('content')
<div class="card page-settings-card">
    <div class="page-settings-head">
        <h2>Site Details</h2>
        <p class="page-settings-sub">Contact details, social links, footer text and the standard sections on every product page.</p>
    </div>

    <form method="POST" action="{{ route('admin.settings.site.update') }}" class="offer-form page-settings-form">
        @csrf
        @method('PUT')

        @foreach($groups as $group)
            <div class="site-settings-group">
                <h3 class="section-title">{{ $group['label'] }}</h3>
                <p class="page-settings-sub">{{ $group['hint'] }}</p>

                @foreach($group['fields'] as $key => $field)
                    <div class="offer-form-row {{ $field['type'] === 'textarea' ? 'offer-form-row-top' : '' }}">
                        <label class="offer-label" for="ss-{{ $key }}">{{ $field['label'] }} <span>:-</span></label>
                        <div class="offer-field form-group">
                            @if($field['type'] === 'textarea')
                                <textarea id="ss-{{ $key }}" name="{{ $key }}" rows="3">{{ old($key, \App\Support\SiteSettings::get($key)) }}</textarea>
                            @else
                                <input id="ss-{{ $key }}" name="{{ $key }}" type="{{ $field['type'] }}" value="{{ old($key, \App\Support\SiteSettings::get($key)) }}" @if($field['type'] === 'url') placeholder="https://" @endif>
                            @endif
                            @if(!empty($field['hint']))<small class="hint d-block">{{ $field['hint'] }}</small>@endif
                            @error($key)<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="offer-form-actions">
            <button type="submit" class="btn btn-primary">Save Site Details</button>
        </div>
    </form>
</div>
@endsection
