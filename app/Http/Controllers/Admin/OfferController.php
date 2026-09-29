<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $query = Offer::query()->latest();

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('discount_percent', 'like', "%{$search}%");
            });
        }

        $offers = $query->paginate(10)->withQueryString();

        return view('admin.offers.index', compact('offers'));
    }

    public function create()
    {
        return view('admin.offers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255', 'unique:offers,title'],
            'description' => ['nullable', 'string'],
            'discount_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'image' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ], [
            'title.unique' => 'This offer name already exists. Duplicate name not allowed.',
        ]);

        $data['slug'] = Str::slug($data['title']);
        $data['image'] = $request->file('image')->store('offers', 'public');

        Offer::create($data);

        return redirect()->route('admin.offers.index')->with('success', 'Offer created successfully.');
    }

    public function edit(Offer $offer)
    {
        return view('admin.offers.edit', compact('offer'));
    }

    public function update(Request $request, Offer $offer)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('offers', 'title')->ignore($offer->id)],
            'description' => ['nullable', 'string'],
            'discount_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ], [
            'title.unique' => 'This offer name already exists. Duplicate name not allowed.',
        ]);

        $data['slug'] = Str::slug($data['title']);

        if ($request->hasFile('image')) {
            if ($offer->image) {
                Storage::disk('public')->delete($offer->image);
            }
            $data['image'] = $request->file('image')->store('offers', 'public');
        }

        $offer->update($data);

        return redirect()->route('admin.offers.index')->with('success', 'Offer updated successfully.');
    }

    public function destroy(Offer $offer)
    {
        if ($offer->image) {
            Storage::disk('public')->delete($offer->image);
        }

        $offer->delete();

        return redirect()->route('admin.offers.index')->with('success', 'Offer deleted successfully.');
    }

    public function toggleStatus(Offer $offer)
    {
        $offer->update(['is_active' => ! $offer->is_active]);

        return back()->with('success', 'Offer status updated.');
    }
}
