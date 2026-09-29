<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MetaCatalogFeedController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $configuredToken = (string) config('services.meta_catalog.feed_token', '');
        $providedToken = (string) $request->query('token', '');

        if ($configuredToken !== '' && ! hash_equals($configuredToken, $providedToken)) {
            abort(403);
        }

        return response()->stream(function (): void {
            $output = fopen('php://output', 'wb');

            fputcsv($output, [
                'id',
                'title',
                'description',
                'availability',
                'condition',
                'link',
                'image_link',
                'brand',
                'price',
                'google_product_category',
                'fb_product_category',
                'quantity_to_sell_on_facebook',
                'sale_price',
                'sale_price_effective_date',
                'item_group_id',
                'gender',
                'color',
                'size',
                'age_group',
                'material',
                'pattern',
                'shipping',
                'shipping_weight',
                'offer_disclaimer',
                'offer_disclaimer_url',
                'video[0].url',
                'video[0].tag[0]',
                'gtin',
                'product_tags[0]',
                'product_tags[1]',
                'style[0]',
            ]);

            Product::query()
                ->active()
                ->with([
                    'brand:id,name',
                    'category:id,title',
                    'subCategory:id,title',
                    'colors:id,name',
                    'sizes:id,name',
                ])
                ->orderBy('id')
                ->chunkById(250, function ($products) use ($output): void {
                    foreach ($products as $product) {
                        fputcsv($output, $this->row($product));
                    }
                });

            fclose($output);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="meta-products.csv"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function row(Product $product): array
    {
        $mrp = (float) $product->mrp;
        $sellingPrice = (float) $product->selling_price;
        $hasSale = $sellingPrice > 0 && $mrp > 0 && $sellingPrice < $mrp;
        $currentPrice = $sellingPrice > 0 ? $sellingPrice : $mrp;

        $description = trim(preg_replace('/\s+/u', ' ', strip_tags(
            (string) ($product->short_description ?: $product->features ?: $product->title)
        )) ?? (string) $product->title);

        return [
            (string) $product->id,
            $product->title,
            mb_substr($description, 0, 9999),
            $this->inventoryQuantity($product) > 0 ? 'in stock' : 'out of stock',
            'new',
            route('shop.single', $product->slug),
            $product->featured_image_url,
            $product->brand?->name ?: config('services.meta_catalog.default_brand', config('app.name')),
            number_format($hasSale ? $mrp : $currentPrice, 2, '.', '').' '.config('services.meta_catalog.currency', 'INR'),
            '',
            '',
            (string) $this->inventoryQuantity($product),
            $hasSale ? number_format($sellingPrice, 2, '.', '').' '.config('services.meta_catalog.currency', 'INR') : '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            $this->shipping($product),
            '',
            '',
            '',
            '',
            '',
            '',
            $product->category?->title ?: '',
            $product->subCategory?->title ?: '',
            '',
        ];
    }

    private function shipping(Product $product): string
    {
        $charge = max(0, (float) $product->delivery_charge);
        $currency = config('services.meta_catalog.currency', 'INR');

        return 'IN::Standard:'.number_format($charge, 2, '.', '').' '.$currency;
    }

    private function inventoryQuantity(Product $product): int
    {
        $colourQuantity = $product->colors->sum(
            fn ($colour) => max(0, (int) $colour->pivot->quantity)
        );
        $sizeQuantity = $product->sizes->sum(
            fn ($size) => max(0, (int) $size->pivot->quantity)
        );

        if ($product->colors->isNotEmpty() && $product->sizes->isNotEmpty()) {
            return min($colourQuantity, $sizeQuantity);
        }

        if ($product->colors->isNotEmpty()) {
            return $colourQuantity;
        }

        if ($product->sizes->isNotEmpty()) {
            return $sizeQuantity;
        }

        return max(1, (int) ($product->max_unit_buy ?: 99));
    }
}
