<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->unsignedBigInteger('entry_id')->nullable()->after('contest_id');
            $table->string('entry_type')->default('Contest')->after('entry_id');
            $table->index(['entry_type', 'entry_id']);
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['entry_type', 'entry_id']);
            $table->dropColumn(['entry_id', 'entry_type']);
        });
    }
};
