@php
    $post = $post ?? null;
@endphp

<div class="form-group">
    <label>Title *</label>
    <input type="text" name="title" value="{{ old('title', $post->title ?? '') }}" maxlength="255" placeholder="Enter journal title">
</div>

<div class="form-group">
    <label>News Type *</label>
    <select name="news_type_id">
        <option value="">-- Select Type --</option>
        @foreach($newsTypes as $type)
            <option value="{{ $type->id }}" @selected(old('news_type_id', $post->news_type_id ?? '') == $type->id)>{{ $type->title }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>Author Name</label>
    <input type="text" name="author_name" value="{{ old('author_name', $post->author_name ?? 'Admin') }}" maxlength="255" placeholder="Admin">
</div>

<div class="form-group">
    <label>Published At</label>
    <input type="datetime-local" name="published_at" value="{{ old('published_at', isset($post) && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
</div>

<div class="form-group">
    <label>Card Image {{ empty($post?->image) ? '*' : '' }}</label>
    @if(!empty($post?->image))
        <div style="margin-bottom:8px;"><img src="{{ $post->image_url }}" alt="" style="max-height:120px;border-radius:8px;"></div>
    @endif
    <input type="file" name="image" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
    <p class="hint">Grid / list thumbnail. Use square image ~448×448 (same size for every post). PNG, JPG, JPEG, WEBP (max 4MB).</p>
</div>

<div class="form-group">
    <label>Banner Image (Journal Banner)</label>
    @if(!empty($post?->banner_image))
        <div style="margin-bottom:8px;"><img src="{{ $post->banner_image_url }}" alt="" style="max-height:120px;border-radius:8px;"></div>
    @endif
    <input type="file" name="banner_image" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
    <p class="hint">Large banner for featured / detail page. Recommended ~1400×549. PNG, JPG, JPEG, WEBP (max 5MB).</p>
</div>

<div class="form-group">
    <label>Short Excerpt</label>
    <textarea name="excerpt" rows="3" maxlength="2000" placeholder="Short summary for listing">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
</div>

<div class="form-group">
    <label>Content / Details *</label>
    <textarea name="content" id="content" rows="12" placeholder="Write post details...">{{ old('content', $post->content ?? '') }}</textarea>
</div>

<div class="form-group">
    <label>Sort Order</label>
    <input type="number" name="sort_order" value="{{ old('sort_order', $post->sort_order ?? 0) }}" min="0" max="9999">
</div>

<div class="form-group">
    <label>Comments Count (display)</label>
    <input type="number" name="comments_count" value="{{ old('comments_count', $post->comments_count ?? 0) }}" min="0" max="999999">
</div>

<div class="toggle-row">
    <span>Active</span>
    <label class="switch">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $post->is_active ?? true) ? 'checked' : '' }}>
        <span class="slider"></span>
    </label>
</div>

<div class="toggle-row">
    <span>Featured Banner Post</span>
    <label class="switch">
        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $post->is_featured ?? false) ? 'checked' : '' }}>
        <span class="slider"></span>
    </label>
</div>
