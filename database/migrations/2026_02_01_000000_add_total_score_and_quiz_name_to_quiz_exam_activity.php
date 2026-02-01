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
        Schema::table('quiz_exam_activity', function (Blueprint $table) {
            $table->integer('total_score')->nullable()->after('score');
            $table->string('quiz_name')->nullable()->after('activity_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_exam_activity', function (Blueprint $table) {
            $table->dropColumn(['total_score', 'quiz_name']);
        });
    }
};
