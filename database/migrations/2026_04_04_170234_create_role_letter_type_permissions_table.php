<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_letter_type_permissions', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['admin', 'direktur', 'wadir', 'kaprodi', 'staf', 'dosen']);
            $table->foreignId('letter_type_id')->constrained('letter_types')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role', 'letter_type_id'], 'role_letter_type_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_letter_type_permissions');
    }
};
