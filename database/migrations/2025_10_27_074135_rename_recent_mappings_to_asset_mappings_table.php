<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('recent_mappings', 'asset_mappings');
    }

    public function down(): void
    {
        Schema::rename('asset_mappings', 'recent_mappings');
    }
};
