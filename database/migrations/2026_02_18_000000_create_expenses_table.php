<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');         // Specific item (e.g., "Uber to Airport")
            $table->decimal('amount', 10, 2);
            $table->string('category');      // Broad Group (e.g., "Travel")
            $table->date('expense_date');
            $table->string('receipt_path')->nullable();
            $table->text('description')->nullable();
            $table->string('reference_no')->nullable(); // Invoice/Transaction ID
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('expenses');
    }
};