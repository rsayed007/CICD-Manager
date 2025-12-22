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
        Schema::table('servers', function (Blueprint $table) {
            $table->string('github_token')->nullable()->after('ssh_key_path');
            $table->string('github_owner')->nullable()->after('github_token');
            $table->string('github_repo')->nullable()->after('github_owner');
            $table->boolean('is_active')->default(true)->after('github_repo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn(['github_token', 'github_owner', 'github_repo', 'is_active']);
        });
    }
};
