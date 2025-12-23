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
        Schema::create('repository_change_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('repo_name')->index();
            $table->foreignId('repository_file_id')->nullable()->constrained('repository_files', 'record_id')->onDelete('cascade');
            $table->enum('notification_type', ['email', 'ui_alert', 'message_log'])->default('message_log');
            $table->enum('change_type', ['new', 'modified', 'deleted'])->index();
            $table->text('file_path');
            $table->text('message');
            $table->boolean('sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->json('metadata')->nullable()->comment('Additional notification data');
            $table->timestamps();
            
            $table->index(['repo_name', 'sent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repository_change_notifications');
    }
};
