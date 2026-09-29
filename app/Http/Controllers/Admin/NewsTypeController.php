<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NewsTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = NewsType::query()->orderBy('sort_order')->orderBy('title')->orderBy('id');

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $newsTypes = $query->paginate(15)->withQueryString();

        return view('admin.news-types.index', compact('newsTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255', 'unique:news_types,title'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = true;

        NewsType::create($data);

        return redirect()->route('admin.news-types.index')->with('success', 'News type created successfully.');
    }

    public function update(Request $request, NewsType $newsType)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('news_types', 'title')->ignore($newsType->id)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $newsType->update($data);

        return redirect()->route('admin.news-types.index')->with('success', 'News type updated successfully.');
    }

    public function destroy(NewsType $newsType)
    {
        $newsType->delete();

        return redirect()->route('admin.news-types.index')->with('success', 'News type deleted successfully.');
    }

    public function toggleStatus(NewsType $newsType)
    {
        $newsType->update(['is_active' => ! $newsType->is_active]);

        return back()->with('success', 'News type status updated.');
    }
}
