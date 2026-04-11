<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('wadir_level')->nullable()->after('role');
            $table->string('jurusan')->nullable()->after('wadir_level');
        });

        Schema::table('letters', function (Blueprint $table) {
            $table->string('target_role')->nullable()->after('target_user_id');
            $table->unsignedTinyInteger('target_wadir_level')->nullable()->after('target_role');
            $table->string('target_jurusan')->nullable()->after('target_wadir_level');
        });
    }

    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropColumn(['target_role', 'target_wadir_level', 'target_jurusan']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['wadir_level', 'jurusan']);
        });
    }
};
