<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('website')->nullable()->after('email');
            $table->string('gst_number')->nullable()->after('website');
            $table->string('address_line1')->nullable()->after('gst_number');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('city')->nullable()->after('address_line2');
            $table->string('state')->nullable()->after('city');
            $table->string('pincode')->nullable()->after('state');
            $table->string('country')->default('India')->after('pincode');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['website', 'gst_number', 'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country']);
        });
    }
};