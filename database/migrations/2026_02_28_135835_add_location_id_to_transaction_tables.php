<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    // Note: We make these nullable() initially so it doesn't crash your existing database records!
    Schema::table('inquiries', function (Blueprint $table) {
        $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
    });
    Schema::table('bookings', function (Blueprint $table) {
        $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
    });
    Schema::table('orders', function (Blueprint $table) {
        $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
    });
    Schema::table('payments', function (Blueprint $table) {
        $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
    });
    Schema::table('expenses', function (Blueprint $table) {
        $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
    });
}

public function down()
{
    $tables = ['inquiries', 'bookings', 'orders', 'payments', 'expenses'];
    
    foreach ($tables as $table) {
        Schema::table($table, function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });
    }
}
};
