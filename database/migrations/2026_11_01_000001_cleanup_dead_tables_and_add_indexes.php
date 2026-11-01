<?php

declare(strict_types=1);

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
        // Drop tables in reverse order due to foreign key constraints
        Schema::dropIfExists('quiz_results');
        Schema::dropIfExists('quizzes_answers');
        Schema::dropIfExists('quizzes');

        // Add missing indexes on contest_votes for faster queries
        Schema::table('contest_votes', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('entry_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore quizzes_answers before quizzes (FK dependency)
        Schema::create('quizzes_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('answers')->nullable();
            $table->timestamps();
        });

        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('questions');
            $table->timestamps();
        });

        Schema::create('quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('score')->default(0);
            $table->integer('total_questions')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['quiz_id', 'user_id']);
        });

        // Remove indexes
        Schema::table('contest_votes', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['entry_id']);
        });
    }
};
