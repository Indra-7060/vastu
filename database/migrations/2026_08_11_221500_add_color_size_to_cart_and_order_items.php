<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cart_items', 'color')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->string('color', 80)->nullable()->after('product_id');
            });
        }

        if (! Schema::hasColumn('cart_items', 'size')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->string('size', 40)->nullable()->after('color');
            });
        }

        // Ensure user_id has its own index before dropping the composite unique
        // (MySQL may reuse that unique index for the user_id foreign key).
        $indexes = DB::getDriverName() === 'sqlite'
            ? collect(DB::select("PRAGMA index_list('cart_items')"))->pluck('name')->unique()->all()
            : collect(DB::select('SHOW INDEX FROM cart_items'))->pluck('Key_name')->unique()->all();

        if (! in_array('cart_items_user_id_index', $indexes, true)) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->index('user_id', 'cart_items_user_id_index');
            });
        }

        if (in_array('cart_items_user_id_product_id_unique', $indexes, true)) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropUnique('cart_items_user_id_product_id_unique');
            });
        }

        if (! Schema::hasColumn('order_items', 'color')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->string('color', 80)->nullable()->after('product_image');
            });
        }

        if (! Schema::hasColumn('order_items', 'size')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->string('size', 40)->nullable()->after('color');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('order_items', 'color') || Schema::hasColumn('order_items', 'size')) {
            Schema::table('order_items', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('order_items', 'color')) {
                    $cols[] = 'color';
                }
                if (Schema::hasColumn('order_items', 'size')) {
                    $cols[] = 'size';
                }
                if ($cols) {
                    $table->dropColumn($cols);
                }
            });
        }

        $indexes = DB::getDriverName() === 'sqlite'
            ? collect(DB::select("PRAGMA index_list('cart_items')"))->pluck('name')->unique()->all()
            : collect(DB::select('SHOW INDEX FROM cart_items'))->pluck('Key_name')->unique()->all();

        if (! in_array('cart_items_user_id_product_id_unique', $indexes, true)) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->unique(['user_id', 'product_id'], 'cart_items_user_id_product_id_unique');
            });
        }

        if (in_array('cart_items_user_id_index', $indexes, true)) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropIndex('cart_items_user_id_index');
            });
        }

        if (Schema::hasColumn('cart_items', 'color') || Schema::hasColumn('cart_items', 'size')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('cart_items', 'color')) {
                    $cols[] = 'color';
                }
                if (Schema::hasColumn('cart_items', 'size')) {
                    $cols[] = 'size';
                }
                if ($cols) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
