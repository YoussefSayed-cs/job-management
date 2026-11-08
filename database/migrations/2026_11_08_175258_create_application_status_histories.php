<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->constrained()->cascadeOnDelete();

            $statuses = ['pending', 'under_review', 'shortlisted', 'interview', 'accepted', 'rejected', 'withdrawn'];
            $table->enum('from_status', $statuses)->nullable();
            $table->enum('to_status', $statuses);

            $table->text('message')->nullable();
            $table->foreignUuid('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_histories');
    }
};