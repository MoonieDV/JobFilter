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
        Schema::create('skill_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('skill_categories')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('category_id')->nullable()->constrained('skill_categories')->nullOnDelete();
            $table->json('aliases')->nullable();
            $table->decimal('popularity_score', 5, 2)->default(0);
            $table->timestamps();

            $table->index(['category_id', 'popularity_score']);
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('company_name');
            $table->string('location');
            $table->string('employment_type')->default('full_time');
            $table->string('experience_level')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->text('description');
            $table->text('responsibilities')->nullable();
            $table->text('requirements')->nullable();
            $table->json('required_skills')->nullable();
            $table->json('preferred_skills')->nullable();
            $table->string('status')->default('open');
            $table->foreignId('posted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['posted_by', 'status']);
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('employer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('location')->nullable();
            $table->string('resume_path')->nullable();
            $table->text('cover_letter')->nullable();
            $table->json('extracted_skills')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('match_score', 5, 2)->nullable();
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();

            $table->index(['job_id', 'status']);
            $table->index(['applicant_id', 'employer_id']);
        });

        Schema::create('application_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('question_id')->nullable();
            $table->text('question_text');
            $table->text('answer')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('type')->default('info');
            $table->boolean('is_read')->default(false);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
        });

        Schema::create('contact_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject');
            $table->text('message');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();

            $table->index('email');
        });

        Schema::create('user_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('skill_id')->nullable()->constrained('skills')->nullOnDelete();
            $table->string('skill_name');
            $table->decimal('confidence_score', 4, 2)->default(1.00);
            $table->string('extracted_from')->default('resume');
            $table->timestamps();

            $table->unique(['user_id', 'skill_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_skills');
        Schema::dropIfExists('contact_submissions');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('application_questions');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('skill_categories');
    }
};
