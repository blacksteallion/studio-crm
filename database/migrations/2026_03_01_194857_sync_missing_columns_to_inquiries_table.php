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
    Schema::table('inquiries', function (Blueprint $table) {
        // Using Schema::hasColumn ensures this doesn't break your live MySQL DB
        // but safely adds the columns to the temporary SQLite testing DB.
        if (!Schema::hasColumn('inquiries', 'lead_source_id')) {
            $table->unsignedBigInteger('lead_source_id')->nullable();
        }
        if (!Schema::hasColumn('inquiries', 'alternate_date')) {
            $table->date('alternate_date')->nullable();
        }
        if (!Schema::hasColumn('inquiries', 'budget')) {
            $table->decimal('budget', 10, 2)->nullable();
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            //
        });
    }
};
