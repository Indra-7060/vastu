<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SizeController extends Controller
{
    public function index(Request $request)
    {
        $query = Size::query()->orderBy('sort_order')->orderBy('name');

        if ($search = trim((string) $request->get('search'))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $sizes = $query->paginate(10)->withQueryString();

        return view('admin.sizes.index', compact('sizes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:sizes,name'],
        ], [
            'name.unique' => 'This size name already exists. Duplicate name not allowed.',
        ]);

        Size::create($data);

        return back()->with('success', 'Size created successfully.');
    }

    public function update(Request $request, Size $size)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('sizes', 'name')->ignore($size->id)],
        ], [
            'name.unique' => 'This size name already exists. Duplicate name not allowed.',
        ]);

        $size->update($data);

        return back()->with('success', 'Size updated successfully.');
    }

    public function destroy(Size $size)
    {
        $size->delete();

        return back()->with('success', 'Size deleted successfully.');
    }

    public function toggleStatus(Size $size)
    {
        $size->update(['is_active' => ! $size->is_active]);

        return back()->with('success', 'Status updated.');
    }
}
