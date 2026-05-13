<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('user_id');
            $table->string('payment_method')->default('cash')->after('status');
            $table->decimal('paid_amount', 10, 2)->default(0)->after('payment_method');
            $table->decimal('change_amount', 10, 2)->default(0)->after('paid_amount');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('change_amount');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('tax_amount');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'payment_method', 'paid_amount', 'change_amount', 'tax_amount', 'discount_amount']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
