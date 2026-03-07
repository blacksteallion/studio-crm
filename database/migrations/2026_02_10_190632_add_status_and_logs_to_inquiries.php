<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Update 'inquiries' table
        Schema::table('inquiries', function (Blueprint $table) {
            // Status Enum (Default: New)
            if (!Schema::hasColumn('inquiries', 'status')) {
                $table->string('status')->default('New')->after('budget'); 
                // Values: New, In Progress, Qualified, Closed-Won, Closed-Lost
            }
            
            // Follow-up Date (For reminders)
            if (!Schema::hasColumn('inquiries', 'follow_up_date')) {
                $table->date('follow_up_date')->nullable()->after('status');
            }
        });

        // 2. Create 'inquiry_logs' table (For History/Timeline)
        if (!Schema::hasTable('inquiry_logs')) {
            Schema::create('inquiry_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inquiry_id')->constrained('inquiries')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Who made the log
                
                $table->string('type'); // Call, Meeting, Email, Note, Status Change
                $table->text('message')->nullable();
                
                $table->date('log_date');
                $table->time('log_time');
                
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('inquiry_logs');
        
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn(['status', 'follow_up_date']);
        });
    }
};