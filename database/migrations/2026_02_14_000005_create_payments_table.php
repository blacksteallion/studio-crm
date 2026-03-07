<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                
                // EXACT columns expected by your PaymentController:
                $table->date('transaction_date');
                $table->decimal('amount', 10, 2);
                $table->string('payment_method');
                $table->string('reference_number')->nullable();
                $table->text('notes')->nullable();
                
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};