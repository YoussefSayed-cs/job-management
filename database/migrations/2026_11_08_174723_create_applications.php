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
            // OPEN in SRS (FR-24, FR-44): placeholder, revisit when decided
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', [ 'pending', 'under_review', 'shortlisted', 'interview', 'accepted', 'rejected', 'withdrawn', ])->default('pending');
            $table->string('job_title');
            $table->string('company_name');
            $table->timestamp('submitted_at');
            $table->timestamps();

            // NOT unique on purpose: re-applying after withdrawal is allowed (FR-90).
            // Duplicate check lives in the Service layer inside a transaction with a lock.
            $table->index(['job_vacancy_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};