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
        Schema::table('evaluations', function (Blueprint $table) {
            // Drop the incorrect foreign key constraint
            $table->dropForeign(['student_id']);
            
            // Drop the student_id column
            $table->dropColumn('student_id');
        });
        
        Schema::table('evaluations', function (Blueprint $table) {
            // Re-add student_id with correct foreign key to students table
            $table->foreignId('student_id')->after('id')->constrained('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            // Drop the correct foreign key
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
        });
        
        Schema::table('evaluations', function (Blueprint $table) {
            // Restore the old incorrect foreign key
            $table->foreignId('student_id')->after('id')->constrained('users')->onDelete('cascade');
        });
    }
};
