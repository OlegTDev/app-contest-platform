<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contest_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entry_id')->constrained('contest_entries')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();

            // Один пользователь может проголосовать только один раз за одну карточку в одном конкурсе
            $table->unique(['contest_id', 'entry_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_votes');
    }
};
