<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recent_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('createdBy')->nullable();        // User email
            $table->string('createdByName')->nullable();    // User name
            $table->string('asset_table_name');
            $table->json('mappings');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recent_mappings');
    }
};
