@extends('admin.layouts.app')

@section('title', ($group === 'gallery' ? 'Gallery' : 'Home Content').' - Vastutathastu')

@section('content')
<div class="dash">
    <header class="dash-head">
        <div>
            @if($group === 'gallery')
                <p class="dash-eyebrow">Gallery</p>
                <h2 class="dash-title">Gallery Photos</h2>
                <p class="dash-sub">The website Gallery has four categories (Our Product Users, Awards, Celebrity, Others), shown as tabs on the Gallery page and in the header's Gallery menu. Add photos to each category below; hide or delete a photo to remove it from the site.</p>
            @else
                <p class="dash-eyebrow">Home Content</p>
                <h2 class="dash-title">Sections &amp; Images</h2>
                <p class="dash-sub">Each section below appears on the website only while it has an active banner. Hide or delete a banner to remove that section from the site.</p>
            @endif
        </div>
        @if($group !== 'gallery')
        <div class="dash-actions">
            <a class="dash-btn" href="{{ route('admin.banners.create') }}">@include('admin.partials.icon', ['name' => 'plus', 'size' => 16]) Add banner</a>
        </div>
        @endif
    </header>

    <div class="cms-sections">
        @foreach($sections as $key => $meta)
            @php
                $items = $bannersBySection->get($key, collect());
                $live = $items->where('is_active', true)->isNotEmpty();
                $state = $items->isEmpty() ? ['Not set', 'neutral'] : ($live ? ['Live', 'success'] : ['Hidden', 'warning']);
            @endphp
            <section class="dash-card cms-section" id="section-{{ $key }}">
                <header class="cms-section__head">
                    <div class="cms-section__info">
                        <div class="cms-section__title">
                            <h3>{{ $meta['label'] }}</h3>
                            <span class="dash-badge dash-badge--{{ $state[1] }}">{{ $state[0] }}</span>
                        </div>
                        <p class="cms-section__page">{{ $meta['page'] }}</p>
                        <p class="cms-section__hint">{{ $meta['hint'] }}</p>
                    </div>
                    @if($meta['multi'] || $items->isEmpty())
                        <a class="dash-btn dash-btn--ghost" href="{{ route('admin.banners.create', ['section' => $key]) }}">@include('admin.partials.icon', ['name' => 'plus', 'size' => 14]) {{ $group === 'gallery' ? ($items->isEmpty() ? 'Add photo' : 'Add another photo') : ($items->isEmpty() ? 'Add' : 'Add another') }}</a>
                    @endif
                </header>

                @if($items->isNotEmpty())
                    <ul class="cms-banners">
                        @foreach($items as $banner)
                            @php $media = $banner->images->first(); @endphp
                            <li class="cms-banner {{ $banner->is_active ? '' : 'is-hidden' }}">
                                <span class="cms-banner__thumb">
                                    @if($media && $media->is_video)
                                        <video src="{{ asset('storage/'.$media->image) }}" muted playsinline preload="metadata"></video>
                                    @elseif($media)
                                        <img src="{{ asset('storage/'.$media->image) }}" alt="">
                                    @else
                                        @include('admin.partials.icon', ['name' => 'journal', 'size' => 18])
                                    @endif
                                </span>
                                @if($group === 'gallery')
                                    {{-- Name the photo / move it to another category without opening the edit page --}}
                                    <form method="POST" action="{{ route('admin.banners.gallery', $banner) }}" class="cms-quick">
                                        @csrf
                                        @method('PATCH')
                                        <label class="cms-quick__field">
                                            <span>Name / caption</span>
                                            <input type="text" name="title" value="{{ $banner->title }}" maxlength="255" placeholder="{{ $key === 'gallery_celebrity' ? 'e.g. Celebrity name' : 'Add a caption (optional)' }}">
                                        </label>
                                        <label class="cms-quick__field cms-quick__field--sm">
                                            <span>Category</span>
                                            <select name="section">
                                                @foreach($sections as $optKey => $optMeta)
                                                    <option value="{{ $optKey }}" @selected($optKey === $banner->section)>{{ \Illuminate\Support\Str::after($optMeta['label'], 'Gallery — ') }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <button type="submit" class="dash-btn dash-btn--sm">Save</button>
                                    </form>
                                @else
                                <span class="cms-banner__text">
                                    <strong>{{ $banner->title !== '' ? $banner->title : 'Photo without caption' }}</strong>
                                    <small>{{ \Illuminate\Support\Str::limit($banner->description ?: ($banner->subtitle ?: ($banner->button_text ? 'Button: '.$banner->button_text : '')), 90) }}</small>
                                </span>
                                @endif
                                <span class="cms-banner__actions">
                                    <form method="POST" action="{{ route('admin.banners.toggle', $banner) }}">
                                        @csrf
                                        @method('PATCH')
                                        <label class="switch" title="{{ $banner->is_active ? 'Shown on website' : 'Hidden from website' }}">
                                            <input type="checkbox" onchange="this.form.submit()" {{ $banner->is_active ? 'checked' : '' }}>
                                            <span class="slider"></span>
                                        </label>
                                    </form>
                                    <a class="dash-btn dash-btn--ghost dash-btn--sm" href="{{ route('admin.banners.edit', $banner) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" class="js-delete-form" data-confirm-title="{{ $group === 'gallery' ? 'Delete this photo?' : 'Delete this banner?' }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dash-btn dash-btn--danger dash-btn--sm">Delete</button>
                                    </form>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endforeach
    </div>
</div>
@endsection
