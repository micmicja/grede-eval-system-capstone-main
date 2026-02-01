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
        Schema::create('teacher_settings', function (Blueprint $table) {
            $table->id();

            // Link settings to the teacher (user)
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Grade weight percentages
            $table->unsignedTinyInteger('quiz_weight')->default(25);
            $table->unsignedTinyInteger('exam_weight')->default(25);
            $table->unsignedTinyInteger('activity_weight')->default(25);
            $table->unsignedTinyInteger('project_weight')->default(15);
            $table->unsignedTinyInteger('recitation_weight')->default(10);
            $table->unsignedTinyInteger('attendance_weight')->default(10);
            $table->timestamps();
            // Ensure one settings record per teacher
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
