<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('job_category_id')->constrained('job_categories')->restrictOnDelete();
            $table->string('title');
            $table->text('description');
            $table->text('requirements');
            $table->string('location');
            $table->enum('employment_type', ['full_time', 'part_time', 'internship', 'freelance', 'contract']);
            $table->enum('work_location_type', ['on_site', 'remote', 'hybrid']);
            $table->text('required_skills')->nullable();
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->enum('experience_level', ['entry_level', 'junior', 'mid_level', 'senior'])->nullable();
            $table->date('application_deadline')->nullable();
            $table->enum('status', ['active', 'closed', 'archived'])->default('active');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('archived_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_vacancies');
    }
};
