<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin → Products Master → Arrange Products: the order of products on each category page
 * (drag and drop), and products from other categories that are also shown on a category page.
 */
class ProductArrangeController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()->orderBy('sort_order')->orderBy('title')->get(['id', 'title', 'slug']);
        $category = $categories->firstWhere('id', (int) $request->query('category')) ?? $categories->first();

        $products = collect();
        $others = collect();
        if ($category) {
            $products = $this->listFor($category);
            $others = Product::query()->where('category_id', '!=', $category->id)
                ->whereNotIn('id', $products->pluck('id'))
                ->with('category:id,title')->orderBy('title')->get(['id', 'title', 'category_id']);
        }

        return view('admin.products.arrange', compact('categories', 'category', 'products', 'others'));
    }

    /** Save the dragged order: every listed product gets its position 1, 2, 3 … */
    public function save(Request $request, Category $category)
    {
        $ids = array_values(array_unique(array_map('intval', (array) $request->input('order', []))));
        $allowed = $this->listFor($category)->pluck('id')->all();

        DB::transaction(function () use ($ids, $allowed, $category) {
            $pos = 0;
            foreach ($ids as $id) {
                if (! in_array($id, $allowed, true)) {
                    continue;
                }
                DB::table('category_product_positions')->updateOrInsert(
                    ['category_id' => $category->id, 'product_id' => $id],
                    ['position' => ++$pos, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        });

        return redirect()->route('admin.products.arrange', ['category' => $category->id])->with('success', 'Order saved — the '.$category->title.' page now shows products in this order.');
    }

    /** Also show a product from another category on this category's page (added at the end). */
    public function add(Request $request, Category $category)
    {
        $data = $request->validate(['product_id' => ['required', 'integer', 'exists:products,id']]);
        $max = (int) DB::table('category_product_positions')->where('category_id', $category->id)->max('position');
        DB::table('category_product_positions')->updateOrInsert(
            ['category_id' => $category->id, 'product_id' => $data['product_id']],
            ['is_extra' => true, 'position' => $max + 1, 'updated_at' => now(), 'created_at' => now()]
        );

        return redirect()->route('admin.products.arrange', ['category' => $category->id])->with('success', 'Product added to '.$category->title.'. Drag it to its place and save.');
    }

    /** Stop showing an added product on this category's page (the product itself is not changed). */
    public function remove(Category $category, Product $product)
    {
        DB::table('category_product_positions')->where(['category_id' => $category->id, 'product_id' => $product->id, 'is_extra' => true])->delete();

        return redirect()->route('admin.products.arrange', ['category' => $category->id])->with('success', $product->title.' removed from '.$category->title.'.');
    }

    /** The category's own products + added ones, in the current order (unpositioned ones after, as on the website). */
    private function listFor(Category $category)
    {
        $pos = DB::table('category_product_positions')->where('category_id', $category->id)->get()->keyBy('product_id');
        $extraIds = $pos->where('is_extra', true)->keys()->all();

        return Product::query()
            ->where(fn ($q) => $q->where('category_id', $category->id)->orWhereIn('id', $extraIds))
            ->with(['category:id,title', 'images'])
            ->orderByDesc('is_new_arrival')->orderBy('sort_order')->orderByDesc('id')
            ->get()
            ->each(function ($p) use ($pos, $category) {
                $p->arrange_position = $pos[$p->id]->position ?? null;
                $p->arrange_extra = $p->category_id !== $category->id;
            })
            ->sortBy(fn ($p) => [$p->arrange_position === null ? 1 : 0, $p->arrange_position ?? 0])
            ->values();
    }
}
