<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('bookings')) {
            Schema::create('bookings', function (Blueprint $table) {
                $table->id();
                
                // Foreign Keys
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('inquiry_id')->nullable();
                $table->unsignedBigInteger('staff_id')->nullable();
                
                // Booking Details
                $table->date('booking_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->string('status')->default('Scheduled');
                $table->text('notes')->nullable();
                
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};