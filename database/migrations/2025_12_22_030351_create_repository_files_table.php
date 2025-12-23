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
        Schema::create('repository_files', function (Blueprint $table) {
            $table->id('record_id');
            $table->string('repo_name')->index();
            $table->string('repo_url');
            $table->string('file_path', 500);
            $table->string('folder_path', 500)->nullable();
            $table->enum('file_type', ['file', 'dir'])->default('file');
            $table->bigInteger('size')->nullable()->comment('File size in bytes');
            $table->string('sha')->nullable()->comment('Git SHA hash');
            $table->timestamp('commit_date')->nullable();
            $table->string('last_commit_sha')->nullable();
            $table->text('last_commit_message')->nullable();
            $table->enum('change_status', ['unchanged', 'new', 'modified', 'deleted'])->default('unchanged');
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['repo_name', 'file_path']);
            $table->index('change_status');
            $table->index('file_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repository_files');
    }
};
