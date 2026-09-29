<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        $query = Color::query()->orderBy('sort_order')->orderBy('name');

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $colors = $query->paginate(10)->withQueryString();

        return view('admin.colors.index', compact('colors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:colors,name'],
            'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
        ], [
            'name.unique' => 'This color name already exists. Duplicate name not allowed.',
        ]);

        Color::create($data);

        return back()->with('success', 'Color created successfully.');
    }

    public function update(Request $request, Color $color)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('colors', 'name')->ignore($color->id)],
            'code' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
        ], [
            'name.unique' => 'This color name already exists. Duplicate name not allowed.',
        ]);

        $color->update($data);

        return back()->with('success', 'Color updated successfully.');
    }

    public function destroy(Color $color)
    {
        $color->delete();

        return back()->with('success', 'Color deleted successfully.');
    }

    public function toggleStatus(Color $color)
    {
        $color->update(['is_active' => ! $color->is_active]);

        return back()->with('success', 'Status updated.');
    }
}
