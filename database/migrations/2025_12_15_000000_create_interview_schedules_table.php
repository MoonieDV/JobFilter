<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('employer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('scheduled_at');
            $table->enum('interview_type', ['online', 'physical'])->default('online');
            $table->text('letter_content')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamps();

            $table->index(['application_id', 'status']);
            $table->index(['employer_id', 'applicant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_schedules');
    }
};
