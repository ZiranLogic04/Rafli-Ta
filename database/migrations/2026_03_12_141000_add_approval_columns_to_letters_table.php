<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->foreignId('target_user_id')->nullable()->after('letter_type_id')->constrained('users')->nullOnDelete();
            $table->string('current_approver_role')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropConstrainedForeignId('target_user_id');
            $table->dropColumn('current_approver_role');
        });
    }
};
