<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('login')->unique()->after('email');
            $table->string('department')->nullable()->after('login');
            $table->string('position')->nullable()->after('department');
            $table->string('city_code')->nullable()->after('position');
            $table->string('phone')->nullable()->after('city_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['login', 'department', 'position', 'city_code', 'phone']);
        });
    }
};
