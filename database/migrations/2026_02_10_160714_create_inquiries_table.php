<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Disable checks to safely drop the table even if linked to logs
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('inquiries');
        Schema::enableForeignKeyConstraints();

        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            
            // NEW: Business Name
            $table->string('business_name')->nullable();

            $table->date('primary_date');
            $table->date('alternate_date')->nullable();
            $table->time('from_time');
            $table->time('to_time');
            $table->decimal('total_hours', 5, 2)->nullable();
            $table->string('budget')->nullable();
            
            // NEW: Assigned Staff (Links to users table)
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('inquiries');
        Schema::enableForeignKeyConstraints();
    }
};