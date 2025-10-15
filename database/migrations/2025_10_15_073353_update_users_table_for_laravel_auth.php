<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add missing Laravel columns safely (only if they don't exist)

            if (!Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable();
            }

            if (!Schema::hasColumn('users', 'email')) {
                $table->string('email')->nullable();
            }

            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable();
            }

            if (!Schema::hasColumn('users', 'password')) {
                $table->string('password')->nullable();
            }

            if (!Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken();
            }

            if (!Schema::hasColumn('users', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (!Schema::hasColumn('users', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }   
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Optional: drop columns if needed (usually you leave them)
            // $table->dropColumn(['email_verified_at', 'remember_token', 'created_at', 'updated_at']);
        });
    }
};
