<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\NewsType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogPostController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::query()->with('newsType')->latest()->orderByDesc('id');

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('author_name', 'like', "%{$search}%");
            });
        }

        if ($typeId = $request->get('news_type_id')) {
            $query->where('news_type_id', $typeId);
        }

        $posts = $query->paginate(10)->withQueryString();
        $newsTypes = NewsType::orderBy('sort_order')->orderBy('title')->get();

        return view('admin.blog-posts.index', compact('posts', 'newsTypes'));
    }

    public function create()
    {
        $newsTypes = NewsType::active()->orderBy('sort_order')->orderBy('title')->get();

        return view('admin.blog-posts.create', compact('newsTypes'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['author_name'] = $data['author_name'] ?: 'Admin';
        $data['published_at'] = $data['published_at'] ?? now();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('blog-posts', 'public');
        }
        if ($request->hasFile('banner_image')) {
            $data['banner_image'] = $request->file('banner_image')->store('blog-posts/banners', 'public');
        }

        BlogPost::create($data);

        return redirect()->route('admin.blog-posts.index')->with('success', 'Journal post created successfully.');
    }

    public function edit(BlogPost $blogPost)
    {
        $newsTypes = NewsType::orderBy('sort_order')->orderBy('title')->get();

        return view('admin.blog-posts.edit', compact('blogPost', 'newsTypes'));
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        $data = $this->validated($request, $blogPost);
        $data['slug'] = $this->uniqueSlug($data['title'], $blogPost->id);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['author_name'] = $data['author_name'] ?: 'Admin';

        if ($request->hasFile('image')) {
            if ($blogPost->image) {
                Storage::disk('public')->delete($blogPost->image);
            }
            $data['image'] = $request->file('image')->store('blog-posts', 'public');
        }
        if ($request->hasFile('banner_image')) {
            if ($blogPost->banner_image) {
                Storage::disk('public')->delete($blogPost->banner_image);
            }
            $data['banner_image'] = $request->file('banner_image')->store('blog-posts/banners', 'public');
        }

        $blogPost->update($data);

        return redirect()->route('admin.blog-posts.index')->with('success', 'Journal post updated successfully.');
    }

    public function destroy(BlogPost $blogPost)
    {
        if ($blogPost->image) {
            Storage::disk('public')->delete($blogPost->image);
        }
        if ($blogPost->banner_image) {
            Storage::disk('public')->delete($blogPost->banner_image);
        }

        $blogPost->delete();

        return redirect()->route('admin.blog-posts.index')->with('success', 'Journal post deleted successfully.');
    }

    public function toggleStatus(BlogPost $blogPost)
    {
        $blogPost->update(['is_active' => ! $blogPost->is_active]);

        return back()->with('success', 'Post status updated.');
    }

    public function toggleFeatured(BlogPost $blogPost)
    {
        $blogPost->update(['is_featured' => ! $blogPost->is_featured]);

        return back()->with('success', 'Featured status updated.');
    }

    protected function validated(Request $request, ?BlogPost $post = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('blog_posts', 'title')->ignore($post?->id)],
            'news_type_id' => ['required', 'exists:news_types,id'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'content' => ['required', 'string'],
            'image' => [$post ? 'nullable' : 'required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'banner_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'comments_count' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'published_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);
    }

    protected function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;
        $i = 1;

        while (
            BlogPost::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
