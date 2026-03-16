<?php

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
        Schema::table('students', function (Blueprint $table) {
            // Add GLOBAL unique constraint on (student_id, first_name, last_name)
            // This ensures: same ID + same name can exist (across all teachers)
            // But: same ID + different names cannot exist (blocked globally)
            $table->unique(['student_id', 'first_name', 'last_name'], 'unique_student_identity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('unique_student_identity');
        });
    }
};
