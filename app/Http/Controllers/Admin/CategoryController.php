<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query()->latest();

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $categories = $query->paginate(10)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255', 'unique:categories,title'],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'has_color' => ['nullable', 'boolean'],
            'has_size' => ['nullable', 'boolean'],
            'show_on_home' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'product_sections' => ['nullable', 'array', 'max:20'],
            'product_sections.*.title' => ['nullable', 'string', 'max:120'],
            'product_sections.*.text' => ['nullable', 'string', 'max:10000'],
        ], [
            'title.unique' => 'This category name already exists. Duplicate name not allowed.',
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['has_color'] = $request->boolean('has_color');
        $data['has_size'] = $request->boolean('has_size');
        $data['show_on_home'] = $request->boolean('show_on_home');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['product_sections'] = $this->productSections($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('categories', 'title')->ignore($category->id)],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'has_color' => ['nullable', 'boolean'],
            'has_size' => ['nullable', 'boolean'],
            'show_on_home' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'product_sections' => ['nullable', 'array', 'max:20'],
            'product_sections.*.title' => ['nullable', 'string', 'max:120'],
            'product_sections.*.text' => ['nullable', 'string', 'max:10000'],
        ], [
            'title.unique' => 'This category name already exists. Duplicate name not allowed.',
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['has_color'] = $request->boolean('has_color');
        $data['has_size'] = $request->boolean('has_size');
        $data['show_on_home'] = $request->boolean('show_on_home');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['product_sections'] = $this->productSections($request);

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }

    public function toggleStatus(Category $category)
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('success', 'Category status updated.');
    }

    /** Sections shown on every product of the category; rows without a title or text are dropped. */
    private function productSections(Request $request): ?array
    {
        $rows = collect($request->input('product_sections', []))
            ->map(fn ($row) => ['title' => trim((string) ($row['title'] ?? '')), 'text' => trim((string) ($row['text'] ?? ''))])
            ->filter(fn ($row) => $row['title'] !== '' && $row['text'] !== '')
            ->values()
            ->all();

        return $rows ?: null;
    }
}
