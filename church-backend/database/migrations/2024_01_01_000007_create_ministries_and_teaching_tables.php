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
        Schema::create('ministries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('leader_name')->nullable();
            $table->foreignId('leader_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['active', 'inactive', 'disbanded'])->default('active');
            $table->timestamps();
        });

        Schema::create('ministry_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ministry_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['member', 'leader', 'assistant'])->default('member');
            $table->date('joined_date');
            $table->timestamps();
            
            $table->unique(['ministry_id', 'user_id']);
        });

        Schema::create('sermons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content')->nullable();
            $table->text('scripture_references')->nullable();
            $table->date('sermon_date');
            $table->foreignId('preacher_id')->constrained('users')->onDelete('cascade');
            $table->string('audio_file')->nullable();
            $table->string('video_file')->nullable();
            $table->string('notes_file')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();
        });

        Schema::create('teaching_notes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->text('scripture_references')->nullable();
            $table->date('teaching_date');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->string('file_path')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching_notes');
        Schema::dropIfExists('sermons');
        Schema::dropIfExists('ministry_members');
        Schema::dropIfExists('ministries');
    }
};
