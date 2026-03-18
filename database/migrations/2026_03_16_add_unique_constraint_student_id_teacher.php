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
        Schema::table('students', function (Blueprint $table) {
            // Add UNIQUE constraint per teacher on (student_id, first_name, last_name, teacher_id)
            // This allows: same student (same ID + name) in different teachers
            // Prevents: duplicate in same teacher
            // Application validation prevents: same ID with different names globally
            $table->unique(['student_id', 'first_name', 'last_name', 'teacher_id'], 'unique_student_per_teacher');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            DB::statement('DROP INDEX IF EXISTS unique_student_identity');
            DB::statement('DROP INDEX IF EXISTS unique_student_per_teacher');
        });
    }
};
