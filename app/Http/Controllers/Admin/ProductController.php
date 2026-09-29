<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Size;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['category', 'subCategory', 'brand', 'offer'])
            ->latest();

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('offer_id')) {
            $query->where('offer_id', $request->offer_id);
        }

        $products = $query->paginate(10)->withQueryString();

        $categories = Category::where('is_active', true)->orderBy('title')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $offers = Offer::where('is_active', true)->orderBy('title')->get();

        return view('admin.products.index', compact('products', 'categories', 'brands', 'offers'));
    }

    public function create(Request $request)
    {
        $data = $this->formData();

        if ($request->filled('copy')) {
            $source = Product::with(['colors', 'sizes'])->find($request->copy);
            if ($source) {
                $data['product'] = $source->replicate(['slug', 'featured_image', 'featured_image_2', 'highlights_image', 'size_guide_image']);
                $data['product']->id = null;
                $data['product']->title = $source->title.' (Copy)';
                $data['product']->setRelation('colors', $source->colors);
                $data['product']->setRelation('sizes', $source->sizes);
                $data['product']->setRelation('images', collect());
            }
        }

        return view('admin.products.create', $data);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['title']);

        $product = DB::transaction(function () use ($request, $data) {
            $data = $this->storeImages($request, $data);
            $product = Product::create($data);
            $this->syncRelations($product, $request);
            $this->storeGallery($request, $product);

            return $product;
        });

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'subCategory', 'brand', 'offer', 'colors', 'sizes', 'images', 'reviews']);

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load(['colors', 'sizes', 'images.color']);

        return view('admin.products.edit', array_merge($this->formData(), compact('product')));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);
        $data['slug'] = $this->uniqueSlug($data['title'], $product->id);

        DB::transaction(function () use ($request, $product, $data) {
            $data = $this->storeImages($request, $data, $product);
            $product->update($data);
            $this->syncRelations($product, $request);
            $this->storeGallery($request, $product);
            $this->deleteGalleryImages($request, $product);
        });

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->deleteProductFiles($product);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('success', 'Product status updated.');
    }

    public function toggleFeatured(Product $product)
    {
        $product->update(['is_featured' => ! $product->is_featured]);

        return back()->with('success', $product->is_featured ? 'Product marked as featured.' : 'Product removed from featured.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => ['required', 'in:enable,disable,delete,set_todays_deal,remove_todays_deal'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
        ]);

        $ids = $request->ids;
        $action = $request->action;
        $count = count($ids);

        switch ($action) {
            case 'enable':
                Product::whereIn('id', $ids)->update(['is_active' => true]);
                $message = "{$count} product(s) enabled.";
                break;
            case 'disable':
                Product::whereIn('id', $ids)->update(['is_active' => false]);
                $message = "{$count} product(s) disabled.";
                break;
            case 'set_todays_deal':
                Product::whereIn('id', $ids)->update(['is_todays_deal' => true]);
                $message = "{$count} product(s) set to today's deal.";
                break;
            case 'remove_todays_deal':
                Product::whereIn('id', $ids)->update(['is_todays_deal' => false]);
                $message = "{$count} product(s) removed from today's deal.";
                break;
            case 'delete':
                $products = Product::whereIn('id', $ids)->get();
                foreach ($products as $product) {
                    $this->deleteProductFiles($product);
                    $product->delete();
                }
                $message = "{$count} product(s) deleted.";
                break;
            default:
                $message = 'Action completed.';
        }

        return back()->with('success', $message);
    }

    public function checkTitle(Request $request)
    {
        $title = trim((string) $request->get('title'));
        $ignoreId = $request->get('ignore_id');

        if ($title === '') {
            return response()->json(['available' => true]);
        }

        $query = Product::where('title', $title);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return response()->json([
            'available' => ! $query->exists(),
            'message' => 'This product title already exists. Duplicate name not allowed.',
        ]);
    }

    public function subCategories(Request $request)
    {
        $subs = SubCategory::where('category_id', $request->get('category_id'))
            ->where('is_active', true)
            ->orderBy('title')
            ->get(['id', 'title']);

        return response()->json($subs);
    }

    public function reviews(Product $product)
    {
        $reviews = $product->reviews()->paginate(10)->withQueryString();

        return view('admin.products.reviews', compact('product', 'reviews'));
    }

    public function storeReview(Request $request, Product $product)
    {
        $data = $request->validate([
            'reviewer_name' => ['required', 'string', 'max:255'],
            'reviewer_email' => ['nullable', 'email', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
        ]);

        $data['is_active'] = true;
        $product->reviews()->create($data);

        return back()->with('success', 'Review added successfully.');
    }

    public function toggleReview(Product $product, ProductReview $review)
    {
        abort_unless($review->product_id === $product->id, 404);
        $review->update(['is_active' => ! $review->is_active]);

        return back()->with('success', 'Review status updated.');
    }

    public function destroyReview(Product $product, ProductReview $review)
    {
        abort_unless($review->product_id === $product->id, 404);
        $review->delete();

        return back()->with('success', 'Review deleted successfully.');
    }

    private function formData(): array
    {
        return [
            'categories' => Category::where('is_active', true)->orderBy('title')->get(),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(),
            'offers' => Offer::where('is_active', true)->orderBy('title')->get(),
            'colors' => Color::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'sizes' => Size::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'subCategories' => SubCategory::where('is_active', true)->orderBy('title')->get(),
        ];
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $titleRule = Rule::unique('products', 'title');
        if ($product) {
            $titleRule = $titleRule->ignore($product->id);
        }

        $imageRule = $product ? ['nullable'] : ['required'];

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255', $titleRule],
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'material' => ['nullable', 'string', 'max:80'],
            'badge' => ['nullable', Rule::in(['New', 'Bestseller', 'Energised', 'Limited Edition'])],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'offer_id' => ['nullable', 'exists:offers,id'],
            'short_description' => ['nullable', 'string'],
            'features' => ['nullable', 'string'],
            'mrp' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'max_unit_buy' => ['required', 'integer', 'min:1'],
            'delivery_charge' => ['required', 'numeric', 'min:0'],
            'featured_image' => array_merge($imageRule, ['image', 'mimes:png,jpg,jpeg', 'max:2048']),
            'featured_image_2' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'color_gallery' => ['nullable', 'array'],
            'color_gallery.*' => ['nullable', 'array'],
            'color_gallery.*.*' => ['image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'color_ids' => ['nullable', 'array'],
            'color_ids.*' => ['integer', 'exists:colors,id'],
            'color_quantities' => ['nullable', 'array'],
            'color_quantities.*' => ['nullable', 'integer', 'min:0'],
            'size_ids' => ['nullable', 'array'],
            'size_ids.*' => ['integer', 'exists:sizes,id'],
            'size_quantities' => ['nullable', 'array'],
            'size_quantities.*' => ['nullable', 'integer', 'min:0'],
            'remove_gallery' => ['nullable', 'array'],
            'remove_gallery.*' => ['integer'],
            'size_guide_content' => ['nullable', 'string'],
            'size_guide_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'highlights_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'highlights_short_description' => ['nullable', 'string'],
            'highlights' => ['nullable', 'array'],
            'highlights.*.title' => ['nullable', 'string', 'max:120'],
            'highlights.*.subtitle' => ['nullable', 'string', 'max:255'],
            'highlights.*.description' => ['nullable', 'string'],
            'highlights.*.existing_icon' => ['nullable', 'string', 'max:255'],
            'highlights.*.icon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'information' => ['nullable', 'array'],
            'information.*.title' => ['nullable', 'string', 'max:120'],
            'information.*.text' => ['nullable', 'string'],
            'specifications' => ['nullable', 'array'],
            'specifications.*.key' => ['nullable', 'string', 'max:120'],
            'specifications.*.value' => ['nullable', 'string', 'max:255'],
            'accessory_packages' => ['nullable', 'array'],
            'accessory_packages.*.label' => ['nullable', 'string', 'max:120'],
            'accessory_packages.*.mrp' => ['nullable', 'numeric', 'min:0'],
            'accessory_packages.*.price' => ['nullable', 'numeric', 'min:0'],
            'enable_accessory_packages' => ['nullable', 'boolean'],
        ], [
            'title.unique' => 'This product title already exists. Duplicate name not allowed.',
            'featured_image.required' => 'Please select Featured Image-1.',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_todays_deal'] = $request->boolean('is_todays_deal');
        $data['is_popular_accessory'] = $request->boolean('is_popular_accessory');
        $data['is_new_arrival'] = $request->boolean('is_new_arrival');
        $data['show_size_guide'] = $request->boolean('show_size_guide');
        $data['accessory_packages'] = $this->buildAccessoryPackages($request, (int) $data['category_id']);
        $data['selling_price'] = $request->filled('selling_price')
            ? (float) $data['selling_price']
            : 0;

        if ($data['selling_price'] > 0 && (float) $data['mrp'] < $data['selling_price']) {
            throw ValidationException::withMessages([
                'mrp' => 'M.R.P. must be greater than or equal to the selling price.',
            ]);
        }

        return $data;
    }

    private function storeImages(Request $request, array $data, ?Product $product = null): array
    {
        if ($request->hasFile('featured_image')) {
            if ($product && $product->featured_image) {
                Storage::disk('public')->delete($product->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')->store('products', 'public');
        } else {
            unset($data['featured_image']);
        }

        if ($request->hasFile('featured_image_2')) {
            if ($product && $product->featured_image_2) {
                Storage::disk('public')->delete($product->featured_image_2);
            }
            $data['featured_image_2'] = $request->file('featured_image_2')->store('products', 'public');
        } else {
            unset($data['featured_image_2']);
        }

        if ($request->hasFile('size_guide_image')) {
            if ($product && $product->size_guide_image) {
                Storage::disk('public')->delete($product->size_guide_image);
            }
            $data['size_guide_image'] = $request->file('size_guide_image')->store('products/size-guides', 'public');
        } else {
            unset($data['size_guide_image']);
        }

        if ($request->hasFile('highlights_image')) {
            if ($product && $product->highlights_image) {
                Storage::disk('public')->delete($product->highlights_image);
            }
            $data['highlights_image'] = $request->file('highlights_image')->store('products/highlights', 'public');
        } else {
            unset($data['highlights_image']);
        }

        $data['highlights_items'] = $this->buildHighlightItems($request, $product);
        $data['information_items'] = $this->buildInformationItems($request);
        $data['specifications'] = $this->buildSpecifications($request);

        unset(
            $data['gallery'],
            $data['color_gallery'],
            $data['color_ids'],
            $data['color_quantities'],
            $data['size_ids'],
            $data['size_quantities'],
            $data['remove_gallery'],
            $data['highlights'],
            $data['information']
        );

        return $data;
    }

    private function buildHighlightItems(Request $request, ?Product $product = null): array
    {
        $rows = $request->input('highlights', []);
        $items = [];

        foreach ($rows as $index => $row) {
            $title = trim((string) ($row['title'] ?? ''));
            $subtitle = trim((string) ($row['subtitle'] ?? ''));
            $description = trim((string) ($row['description'] ?? ''));
            $existingIcon = trim((string) ($row['existing_icon'] ?? ''));

            if ($title === '' && $subtitle === '' && $description === '' && ! $request->hasFile("highlights.$index.icon")) {
                continue;
            }

            $icon = $existingIcon !== '' ? $existingIcon : null;
            if ($request->hasFile("highlights.$index.icon")) {
                if ($icon) {
                    Storage::disk('public')->delete($icon);
                }
                $icon = $request->file("highlights.$index.icon")->store('products/highlights/icons', 'public');
            }

            $items[] = [
                'icon' => $icon,
                'title' => $title,
                'subtitle' => $subtitle,
                'description' => $description,
            ];
        }

        return $items;
    }

    private function buildInformationItems(Request $request): array
    {
        $items = [];
        foreach ($request->input('information', []) as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            $text = trim((string) ($row['text'] ?? ''));
            if ($title === '' && $text === '') {
                continue;
            }
            $items[] = [
                'title' => $title,
                'text' => $text,
            ];
        }

        return $items;
    }

    private function buildSpecifications(Request $request): array
    {
        $items = [];
        foreach ($request->input('specifications', []) as $row) {
            $key = trim((string) ($row['key'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));
            if ($key === '' && $value === '') {
                continue;
            }
            $items[] = [
                'key' => $key,
                'value' => $value,
            ];
        }

        return $items;
    }

    private function buildAccessoryPackages(Request $request, int $categoryId): array
    {
        $category = Category::query()->find($categoryId);
        $slug = strtolower((string) optional($category)->slug);
        if (! in_array($slug, ['accessories', 'accessory'], true)) {
            return [];
        }

        if (! $request->boolean('enable_accessory_packages')) {
            return [];
        }

        $items = [];
        foreach ($request->input('accessory_packages', []) as $index => $row) {
            $label = trim((string) ($row['label'] ?? ''));
            $mrp = $row['mrp'] ?? null;
            $price = $row['price'] ?? null;
            if ($label === '' || $price === null || $price === '') {
                continue;
            }

            if ($mrp !== null && $mrp !== '' && (float) $mrp < (float) $price) {
                throw ValidationException::withMessages([
                    "accessory_packages.{$index}.mrp" => 'Package M.R.P. must be greater than or equal to its selling price.',
                ]);
            }

            $items[] = [
                'key' => 'package-'.($index + 1),
                'label' => $label,
                'mrp' => (float) ($mrp !== null && $mrp !== '' ? $mrp : $price),
                'price' => (float) $price,
            ];
        }

        return $items;
    }

    private function syncRelations(Product $product, Request $request): void
    {
        $colors = collect($request->input('color_ids', []))->mapWithKeys(
            fn ($colorId) => [(int) $colorId => [
                'quantity' => (int) $request->input("color_quantities.{$colorId}", 0),
            ]]
        );

        $sizes = collect($request->input('size_ids', []))->mapWithKeys(
            fn ($sizeId) => [(int) $sizeId => [
                'quantity' => (int) $request->input("size_quantities.{$sizeId}", 0),
            ]]
        );

        $product->colors()->sync($colors->all());
        $product->sizes()->sync($sizes->all());
    }

    private function storeGallery(Request $request, Product $product): void
    {
        $sort = (int) $product->images()->max('sort_order');

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                $sort++;
                $product->images()->create([
                    'color_id' => null,
                    'image' => $file->store('products/gallery', 'public'),
                    'sort_order' => $sort,
                ]);
            }
        }

        foreach ($request->file('color_gallery', []) as $colorId => $files) {
            if (! is_array($files)) {
                continue;
            }

            foreach ($files as $file) {
                if (! $file) {
                    continue;
                }

                $sort++;
                $product->images()->create([
                    'color_id' => (int) $colorId ?: null,
                    'image' => $file->store('products/gallery', 'public'),
                    'sort_order' => $sort,
                ]);
            }
        }
    }

    private function deleteGalleryImages(Request $request, Product $product): void
    {
        $ids = $request->input('remove_gallery', []);
        if (! $ids) {
            return;
        }

        $images = $product->images()->whereIn('id', $ids)->get();
        foreach ($images as $image) {
            Storage::disk('public')->delete($image->image);
            $image->delete();
        }
    }

    private function deleteProductFiles(Product $product): void
    {
        if ($product->featured_image) {
            Storage::disk('public')->delete($product->featured_image);
        }
        if ($product->featured_image_2) {
            Storage::disk('public')->delete($product->featured_image_2);
        }
        if ($product->size_guide_image) {
            Storage::disk('public')->delete($product->size_guide_image);
        }
        if ($product->highlights_image) {
            Storage::disk('public')->delete($product->highlights_image);
        }
        foreach ($product->highlights_items ?? [] as $item) {
            if (! empty($item['icon'])) {
                Storage::disk('public')->delete($item['icon']);
            }
        }
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image);
        }
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'product';
        $slug = $base;
        $i = 1;

        while (
            Product::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
