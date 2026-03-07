<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Update Users Table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'mobile')) {
                    $table->string('mobile')->nullable()->after('email');
                }
                if (!Schema::hasColumn('users', 'role')) {
                    $table->string('role')->default('staff')->after('password');
                }
                if (!Schema::hasColumn('users', 'status')) {
                    $table->boolean('status')->default(1)->after('role');
                }
            });
        }

        // 2. Customers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('mobile');
            $table->string('business_name')->nullable();
            $table->text('remarks')->nullable();
            $table->string('password')->nullable();
            $table->boolean('status')->default(1); // FIX: Added missing status column
            $table->timestamps();
        });

        // 3. Services
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_per_slot', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        // 4. Inquiries
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers'); // FIX: Added mapping
            $table->string('customer_name')->nullable(); 
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('business_name')->nullable();
            $table->date('primary_date')->nullable();
            $table->date('alternate_date')->nullable();
            $table->time('from_time')->nullable();
            $table->time('to_time')->nullable();
            $table->decimal('total_hours', 8, 2)->nullable();
            $table->decimal('budget', 10, 2)->nullable();
            $table->unsignedBigInteger('lead_source_id')->nullable();
            $table->string('status')->default('New');
            $table->unsignedBigInteger('assigned_staff_id')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // 5. Inquiry Logs
        Schema::create('inquiry_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users'); // FIX: Matched Controller's user_id
            $table->date('log_date')->nullable(); // FIX: Matched Controller's date/time
            $table->time('log_time')->nullable(); 
            $table->text('message');
            $table->string('type')->default('Note');
            $table->timestamps();
        });

        // 6. Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('inquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('staff_id')->constrained('users');
            $table->date('booking_date');
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->string('status')->default('Confirmed');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // 7. Order Items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            
            // FIX: Added the newer columns here natively so SQLite testing doesn't crash!
            $table->foreignId('service_id')->nullable(); 
            $table->string('item_name')->nullable();
            $table->decimal('amount', 10, 2)->default(0.00);
            
            $table->time('slot_start_time')->nullable();
            $table->time('slot_end_time')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('inquiry_logs');
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('services');
        Schema::dropIfExists('customers');
    }
};