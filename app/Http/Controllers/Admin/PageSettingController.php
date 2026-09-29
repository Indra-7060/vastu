<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\SitePages;
use Illuminate\Http\Request;

class PageSettingController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePagesExist();

        $slug = (string) $request->get('page', 'about-us');
        if (! SitePages::isValid($slug)) {
            $slug = 'about-us';
        }

        $page = Page::where('slug', $slug)->firstOrFail();
        $groups = SitePages::groups();

        return view('admin.settings.pages', compact('page', 'groups', 'slug'));
    }

    public function update(Request $request, Page $page)
    {
        if (! SitePages::isValid($page->slug)) {
            abort(404);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Page title is required.',
        ]);

        $page->update([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.settings.pages', ['page' => $page->slug])
            ->with('success', SitePages::label($page->slug).' saved successfully.');
    }

    private function ensurePagesExist(): void
    {
        foreach (SitePages::all() as $slug => $title) {
            Page::firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'content' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
