<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('offer_type', 40)->default('coupon')->after('code');
            $table->string('applies_to', 20)->default('all')->after('min_cart_amount');
            $table->json('category_ids')->nullable()->after('applies_to');
            $table->json('product_ids')->nullable()->after('category_ids');
            $table->unsignedInteger('min_quantity')->nullable()->after('product_ids');
            $table->boolean('new_customers_only')->default(false)->after('min_quantity');
            $table->boolean('members_only')->default(false)->after('new_customers_only');
            $table->boolean('free_shipping')->default(false)->after('members_only');
            $table->boolean('prepaid_only')->default(false)->after('free_shipping');
            $table->timestamp('starts_at')->nullable()->after('prepaid_only');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
            $table->unsignedInteger('usage_limit')->nullable()->after('ends_at');
            $table->unsignedInteger('used_count')->default(0)->after('usage_limit');
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'coupon_code')) {
                $table->string('coupon_code', 50)->nullable()->after('discount_amount');
            }
            if (! Schema::hasColumn('orders', 'coupon_id')) {
                $table->foreignId('coupon_id')->nullable()->after('coupon_code')->constrained('coupons')->nullOnDelete();
            }
        });

        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 50);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_redemptions');

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'coupon_id')) {
                $table->dropConstrainedForeignId('coupon_id');
            }
            if (Schema::hasColumn('orders', 'coupon_code')) {
                $table->dropColumn('coupon_code');
            }
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn([
                'offer_type',
                'applies_to',
                'category_ids',
                'product_ids',
                'min_quantity',
                'new_customers_only',
                'members_only',
                'free_shipping',
                'prepaid_only',
                'starts_at',
                'ends_at',
                'usage_limit',
                'used_count',
            ]);
        });
    }
};
