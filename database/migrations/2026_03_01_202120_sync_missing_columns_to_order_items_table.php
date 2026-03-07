<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('order_items', function (Blueprint $table) {
        // Safely add missing columns to the SQLite testing database
        if (!Schema::hasColumn('order_items', 'item_name')) {
            $table->string('item_name')->nullable();
        }
        if (!Schema::hasColumn('order_items', 'quantity')) {
            $table->integer('quantity')->default(1);
        }
        if (!Schema::hasColumn('order_items', 'unit_price')) {
            $table->decimal('unit_price', 10, 2)->default(0);
        }
        if (!Schema::hasColumn('order_items', 'gst_rate')) {
            $table->decimal('gst_rate', 5, 2)->default(0);
        }
        if (!Schema::hasColumn('order_items', 'gst_amount')) {
            $table->decimal('gst_amount', 10, 2)->default(0);
        }
        if (!Schema::hasColumn('order_items', 'amount')) {
            $table->decimal('amount', 10, 2)->default(0);
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            //
        });
    }
};
