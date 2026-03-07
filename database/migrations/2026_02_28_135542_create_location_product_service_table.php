<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('location_product_service', function (Blueprint $table) {
        $table->id();
        $table->foreignId('location_id')->constrained()->onDelete('cascade');
        $table->foreignId('product_service_id')->constrained()->onDelete('cascade');
        $table->decimal('price', 10, 2)->default(0.00); // The specific price for this location
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('location_product_service');
}
};
