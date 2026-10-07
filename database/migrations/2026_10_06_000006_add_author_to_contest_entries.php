<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contest_entries', function (Blueprint $table) {
            $table->string('author_name')->nullable()->after('description');
            $table->string('author_department')->nullable()->after('author_name');
        });
    }

    public function down(): void
    {
        Schema::table('contest_entries', function (Blueprint $table) {
            $table->dropColumn(['author_name', 'author_department']);
        });
    }
};
