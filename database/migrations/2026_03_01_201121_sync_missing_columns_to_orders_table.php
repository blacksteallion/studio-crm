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
    Schema::table('orders', function (Blueprint $table) {
        // Safely add missing columns to the SQLite testing database
        if (!Schema::hasColumn('orders', 'booking_id')) {
            $table->unsignedBigInteger('booking_id')->nullable();
        }
        if (!Schema::hasColumn('orders', 'invoice_number')) {
            $table->string('invoice_number')->nullable();
        }
        if (!Schema::hasColumn('orders', 'invoice_date')) {
            $table->date('invoice_date')->nullable();
        }
        if (!Schema::hasColumn('orders', 'due_date')) {
            $table->date('due_date')->nullable();
        }
        if (!Schema::hasColumn('orders', 'subtotal')) {
            $table->decimal('subtotal', 10, 2)->default(0);
        }
        if (!Schema::hasColumn('orders', 'tax')) {
            $table->decimal('tax', 10, 2)->default(0);
        }
        if (!Schema::hasColumn('orders', 'discount')) {
            $table->decimal('discount', 10, 2)->default(0);
        }
        if (!Schema::hasColumn('orders', 'notes')) {
            $table->text('notes')->nullable();
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
