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
		Schema::create('whatsapp_numbers', function (Blueprint $table) {
			$table->id();
			$table->string('phone_number_id')->unique();
			$table->string('phone_number_name'); // e.g., "Main Studio", "Wedding Dept"
			$table->text('access_token');
			$table->string('welcome_template_name');
			
			// This links the specific WhatsApp number to a specific staff member
			$table->unsignedBigInteger('assigned_staff_id')->nullable();
			$table->foreign('assigned_staff_id')->references('id')->on('users')->nullOnDelete();
			
			$table->boolean('is_active')->default(true);
			$table->timestamps();
		});
	}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_numbers');
    }
};
