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
    Schema::create('locations', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('address')->nullable();
        $table->string('contact_number')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('locations');
}
};
