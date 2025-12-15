<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_training_data', function (Blueprint $table) {
            $table->id();
            $table->string('skill_name')->index();
            $table->string('category')->nullable();
            $table->integer('frequency')->default(1);
            $table->json('aliases')->nullable();
            $table->timestamps();
            
            $table->unique('skill_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_training_data');
    }
};
