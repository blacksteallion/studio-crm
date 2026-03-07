<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Master Catalog Table
        Schema::create('product_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['Service', 'Product'])->default('Service');
            $table->enum('pricing_model', ['Hourly', 'Fixed', 'Per Unit'])->default('Fixed');
            $table->decimal('price', 10, 2)->default(0.00);
            
            // Added GST Rate
            $table->decimal('gst_rate', 5, 2)->default(0.00); 
            
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Inquiry Items (The "Wishlist")
        Schema::create('inquiry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_service_id')->constrained('product_services'); // Link to master
            $table->string('item_name'); // Snapshot of name in case master changes
            $table->decimal('unit_price', 10, 2); // Snapshot of price
            $table->decimal('quantity', 8, 2)->default(1); // Hours or Qty
            
            // Added GST details for the line item
            $table->decimal('gst_rate', 5, 2)->default(0.00);
            $table->decimal('gst_amount', 10, 2)->default(0.00);
            
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });

        // 3. Booking Items (The "Committed Scope")
        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_service_id')->constrained('product_services');
            $table->string('item_name');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('quantity', 8, 2)->default(1);
            
            // Added GST details for the line item
            $table->decimal('gst_rate', 5, 2)->default(0.00);
            $table->decimal('gst_amount', 10, 2)->default(0.00);
            
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_items');
        Schema::dropIfExists('inquiry_items');
        Schema::dropIfExists('product_services');
    }
};