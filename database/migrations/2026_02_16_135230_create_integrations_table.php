<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->string('platform')->index(); // e.g., 'meta'
            
            // Identity & Auth
            $table->string('page_id')->nullable();   // The Facebook Page ID we are syncing
            $table->string('page_name')->nullable(); // Friendly name for the UI (e.g. "AddFrame Studio")
            $table->text('access_token')->nullable(); // Encrypted Long-Lived Token
            
            // Configuration
            $table->json('field_mapping')->nullable(); // Stores logic: {"full_name": "name", "email": "email"}
            $table->boolean('is_active')->default(false);
            
            // Status Tracking
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_error')->nullable(); // Good for debugging webhook failures
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('integrations');
    }
};