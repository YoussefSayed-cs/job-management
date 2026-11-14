<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('job_vacancy_id')->constrained('job_vacancies')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'job_vacancy_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_jobs');
    }
};
