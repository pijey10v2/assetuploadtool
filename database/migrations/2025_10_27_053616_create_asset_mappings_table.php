<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only create table if it doesn't already exist
        if (!Schema::hasTable('asset_mappings')) {
            Schema::create('asset_mappings', function (Blueprint $table) {
                $table->id();
                $table->string('createdBy')->nullable();        // User email
                $table->string('createdByName')->nullable();    // User name
                $table->string('asset_table_name');
                $table->json('mappings');
                $table->timestamps();
            });
        } else {
            // Optionally: ensure new columns exist (for incremental updates)
            Schema::table('asset_mappings', function (Blueprint $table) {
                if (!Schema::hasColumn('asset_mappings', 'createdBy')) {
                    $table->string('createdBy')->nullable();
                }
                if (!Schema::hasColumn('asset_mappings', 'createdByName')) {
                    $table->string('createdByName')->nullable();
                }
                if (!Schema::hasColumn('asset_mappings', 'asset_table_name')) {
                    $table->string('asset_table_name')->nullable();
                }
                if (!Schema::hasColumn('asset_mappings', 'mappings')) {
                    $table->json('mappings')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_mappings');
    }
};
