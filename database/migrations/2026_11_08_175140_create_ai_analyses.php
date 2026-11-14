<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_analyses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->unsignedInteger('match_score')->nullable();
            $table->text('feedback')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('failure_reason')->nullable();
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_analyses');
    }
};
