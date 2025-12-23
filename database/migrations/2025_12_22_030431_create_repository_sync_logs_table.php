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
        Schema::create('repository_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('repo_name')->index();
            $table->string('repo_url');
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('runtime_seconds')->nullable();
            $table->integer('files_scanned')->default(0);
            $table->integer('new_files')->default(0);
            $table->integer('modified_files')->default(0);
            $table->integer('deleted_files')->default(0);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['repo_name', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repository_sync_logs');
    }
};
