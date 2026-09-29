<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Support\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Replaces the demo catalogue with the real Vastutathastu catalogue.
 *
 *   php artisan vastu:import-live /path/to/dataset.json --wipe-demo
 *
 * dataset.json is produced by the migration script (live vastutathastu.com products + the owner's
 * photo archive). Photos are copied into public/storage/products and optimised (max 1600 px).
 */
class ImportLiveCatalog extends Command
{
    protected $signature = 'vastu:import-live {dataset : Path to dataset.json} {--wipe-demo : Remove demo products, content, orders and test accounts first}';

    protected $description = 'Import the real Vastutathastu catalogue (and remove the demo data)';

    /** Test/demo customer accounts to remove (the admin account is never touched). */
    private const TEST_EMAILS = ['manu@example.com', 'customer@test.com', 'john@gmail.com'];

    /** Categories shown in "Shop by Category" on the homepage, in this order. */
    private const HOME_CATEGORIES = ['rudraksh', 'bracelet', 'maala', 'pendant', 'yantra', 'murti', 'tree', 'crystals'];

    public function handle(): int
    {
        $path = $this->argument('dataset');
        if (! is_file($path)) {
            $this->error("Dataset not found: {$path}");

            return self::FAILURE;
        }
        $base = dirname(realpath($path));
        $data = json_decode(file_get_contents($path), true);

        if ($this->option('wipe-demo')) {
            $this->wipeDemo();
        }

        $productDir = public_path('storage/products');
        $categoryDir = public_path('storage/categories');
        File::ensureDirectoryExists($productDir);
        File::ensureDirectoryExists($categoryDir);

        // Categories
        $categories = [];
        foreach ($data['categories'] as $i => $cat) {
            $home = array_search($cat['slug'], self::HOME_CATEGORIES, true);
            $categories[$cat['slug']] = Category::create([
                'title' => $cat['title'],
                'slug' => $cat['slug'],
                'is_active' => true,
                'show_on_home' => $home !== false,
                'sort_order' => $home !== false ? $home + 1 : 20 + $i,
                'has_color' => false,
                'has_size' => false,
            ]);
        }
        $this->info(count($categories).' categories created.');

        // Products
        $bar = $this->output->createProgressBar(count($data['products']));
        $categoryImage = [];
        foreach ($data['products'] as $i => $row) {
            $sources = array_values(array_filter(array_merge(
                $row['image_files'] ?? [],
                array_map(fn ($p) => $base.'/'.$p, $row['image_downloads'] ?? [])
            ), 'is_file'));

            $stored = [];
            foreach ($sources as $n => $src) {
                $stored[] = $this->storeImage($src, 'products', $row['slug'].'-'.($n + 1));
            }
            $stored = array_values(array_filter($stored));

            $price = (float) $row['price'];
            $mrp = max((float) $row['mrp'], $price);
            $description = trim((string) $row['description']);

            $product = Product::create([
                'title' => $row['title'],
                'slug' => $row['slug'],
                'category_id' => $categories[$row['category']]->id ?? null,
                'sub_category_id' => null,
                'material' => $row['material'] ?: null,
                'badge' => null,
                'short_description' => $description ?: null,
                'features' => $row['features'] ? implode("\n", $row['features']) : null,
                'specifications' => $row['specifications'] ?: null,
                'information_items' => $row['sections'] ?: null,
                'highlights_items' => null,
                'mrp' => $mrp,
                'selling_price' => $price,
                'featured_image' => $stored[0] ?? null,
                'featured_image_2' => $stored[1] ?? null,
                'is_active' => true,
                'is_featured' => (bool) ($row['is_featured'] ?? false),
                'is_new_arrival' => false,
                'seo_title' => $row['title'].' | Vastutathastu',
                'meta_description' => $description ? Str::limit(preg_replace('/\s+/', ' ', $description), 155, '') : null,
                'sort_order' => $i + 1,
            ]);

            foreach (array_slice($stored, 2) as $n => $img) {
                $product->images()->create(['image' => $img, 'sort_order' => $n + 1]);
            }

            if (! isset($categoryImage[$row['category']]) && $stored) {
                $categoryImage[$row['category']] = $stored[0];
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        // Category tiles use the first product photo of each category.
        foreach ($categoryImage as $slug => $img) {
            $copy = 'categories/'.$slug.'.'.pathinfo($img, PATHINFO_EXTENSION);
            File::copy(public_path('storage/'.$img), public_path('storage/'.$copy));
            $categories[$slug]->update(['image' => $copy]);
        }

        $this->info(count($data['products']).' products imported.');

        return self::SUCCESS;
    }

    /** Copy + optimise one photo; returns the storage-relative path. */
    private function storeImage(string $src, string $dir, string $name): ?string
    {
        $info = @getimagesize($src);
        if (! $info) {
            return null;
        }
        $tmp = tempnam(sys_get_temp_dir(), 'vtimg');
        copy($src, $tmp);
        $stats = ImageOptimizer::optimize($tmp, 1600);
        $mime = $stats['mime'] ?? $info['mime'];
        $ext = match ($mime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
        $relative = $dir.'/'.$name.'.'.$ext;
        File::move($tmp, public_path('storage/'.$relative));
        @chmod(public_path('storage/'.$relative), 0644);

        return $relative;
    }

    private function wipeDemo(): void
    {
        $this->warn('Removing demo data…');
        $tables = ['order_status_logs', 'coupon_redemptions', 'order_items', 'orders', 'cart_items', 'wishlists',
            'product_reviews', 'product_color', 'product_size', 'product_images', 'products', 'sub_categories',
            'categories', 'blog_posts', 'news_types', 'stores'];

        Schema::disableForeignKeyConstraints();
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                DB::table($table)->delete();
                $this->line("  {$table}: {$count} removed");
            }
        }
        Schema::enableForeignKeyConstraints();

        $users = DB::table('users')->whereIn('email', self::TEST_EMAILS)->where('role', '!=', 'admin');
        $this->line('  test customer accounts: '.$users->count().' removed');
        $ids = $users->pluck('id');
        foreach (['user_addresses', 'user_bank_accounts', 'consultations'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'user_id')) {
                DB::table($table)->whereIn('user_id', $ids)->delete();
            }
        }
        if (Schema::hasTable('notifications')) {
            DB::table('notifications')->where('notifiable_type', 'App\\Models\\User')->whereIn('notifiable_id', $ids)->delete();
        }
        DB::table('users')->whereIn('id', $ids)->delete();

        // Demo newsletter / contact entries
        foreach (['subscribers' => 'email', 'contact_messages' => 'email'] as $table => $col) {
            if (Schema::hasTable($table)) {
                $n = DB::table($table)->where(fn ($q) => $q->where($col, 'like', '%@example.com')->orWhere($col, 'like', '%.test@%')->orWhere($col, 'hello@shopper.in'))->delete();
                $this->line("  {$table} (demo): {$n} removed");
            }
        }

        // Demo image files
        foreach (['products', 'categories', 'blog-posts'] as $dir) {
            $full = public_path('storage/'.$dir);
            if (is_dir($full)) {
                File::deleteDirectory($full);
                $this->line("  files: storage/{$dir} removed");
            }
        }
    }
}
