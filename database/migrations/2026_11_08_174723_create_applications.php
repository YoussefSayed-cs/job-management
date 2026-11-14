<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_vacancy_id')->nullable()->constrained('job_vacancies')->nullOnDelete();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignUuid('user_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['pending', 'under_review', 'shortlisted', 'interview', 'accepted', 'rejected', 'withdrawn'])->default('pending');
            $table->string('job_title');
            $table->string('company_name');
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->index(['job_vacancy_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
