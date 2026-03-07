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
        Schema::table('users', function (Blueprint $table) {
            
            // 1. Add 'role' if it's missing (Needed for Staff vs Admin)
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('staff')->after('id');
            }

            // 2. Add 'mobile' if it's missing (Fixes your current error)
            if (!Schema::hasColumn('users', 'mobile')) {
                $table->string('mobile')->nullable()->after('email');
            }

            // 3. Add 'photo' if it's missing
            if (!Schema::hasColumn('users', 'photo')) {
                $table->string('photo')->nullable()->after('email');
            }

            // 4. Add 'status' if it's missing (Needed for your Toggle Button)
            if (!Schema::hasColumn('users', 'status')) {
                $table->boolean('status')->default(1)->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'mobile', 'photo', 'status']);
        });
    }
};