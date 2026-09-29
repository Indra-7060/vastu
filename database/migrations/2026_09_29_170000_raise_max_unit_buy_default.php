<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The imported catalogue kept the column default of 1, so no product could be added to the bag
     * more than once. Allow up to 10 per order (still editable per product: "Max Unit Buy").
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE products ALTER COLUMN max_unit_buy SET DEFAULT 10');
        DB::table('products')->where('max_unit_buy', '<=', 1)->update(['max_unit_buy' => 10]);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE products ALTER COLUMN max_unit_buy SET DEFAULT 1');
    }
};
