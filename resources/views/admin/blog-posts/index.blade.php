@extends('admin.layouts.app')

@section('title', 'Journal Posts - Vastutathastu')

@section('content')
<div class="page-head">
    <h2>Journal Posts</h2>
    <div class="page-head-actions">
        <form method="GET" action="{{ route('admin.blog-posts.index') }}" id="module-search-form">
            <div class="search-box">
                <span>@include('admin.partials.icon', ['name' => 'search', 'size' => 16])</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search posts..." autocomplete="off">
            </div>
            @if(request('news_type_id'))
                <input type="hidden" name="news_type_id" value="{{ request('news_type_id') }}">
            @endif
        </form>
        <a href="{{ route('admin.blog-posts.create') }}" class="btn btn-primary">Add Post</a>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <form method="GET" action="{{ route('admin.blog-posts.index') }}" class="product-filters">
        @if(request('search'))
            <input type="hidden" name="search" value="{{ request('search') }}">
        @endif
        <select name="news_type_id" onchange="this.form.submit()">
            <option value="">---All News Types---</option>
            @foreach($newsTypes as $type)
                <option value="{{ $type->id }}" @selected(request('news_type_id') == $type->id)>{{ $type->title }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Featured</th>
                    <th>Status</th>
                    <th>Published</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                <tr>
                    <td>{{ $posts->firstItem() + $loop->index }}</td>
                    <td>
                        <img src="{{ $post->image_url }}" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:6px;">
                    </td>
                    <td>
                        <strong>{{ $post->title }}</strong><br>
                        <small>{{ $post->slug }}</small>
                    </td>
                    <td>{{ $post->newsType->title ?? '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.blog-posts.featured', $post) }}">
                            @csrf
                            @method('PATCH')
                            <label class="switch">
                                <input type="checkbox" onchange="this.form.submit()" {{ $post->is_featured ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.blog-posts.toggle', $post) }}">
                            @csrf
                            @method('PATCH')
                            <label class="switch">
                                <input type="checkbox" onchange="this.form.submit()" {{ $post->is_active ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </form>
                    </td>
                    <td>{{ optional($post->published_at)->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.blog-posts.edit', $post) }}" class="action-sq" title="Edit" aria-label="Edit">@include('admin.partials.icon', ['name' => 'edit', 'size' => 16])</a>
                        <form method="POST" action="{{ route('admin.blog-posts.destroy', $post) }}" class="js-delete-form" data-confirm-title="Delete this post?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-sq action-del" title="Delete" aria-label="Delete">@include('admin.partials.icon', ['name' => 'trash', 'size' => 16])</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8"><div class="empty-state">No journal posts found.</div></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination-wrap">
    {{ $posts->links() }}
</div>
@endsection
