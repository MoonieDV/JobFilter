<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'interview_scheduled_at')) {
                $table->dateTime('interview_scheduled_at')->nullable()->after('applied_at');
            }
            if (!Schema::hasColumn('applications', 'interview_type')) {
                $table->string('interview_type')->nullable()->after('interview_scheduled_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['interview_scheduled_at', 'interview_type']);
        });
    }
};
