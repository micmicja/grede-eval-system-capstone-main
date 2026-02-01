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
        //
        Schema::create('quiz_exam_activity', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('subject');
            $table->string('section');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('activity_type'); // 'quiz' or 'exam'
            $table->string('activity_title');
            $table->date('date_taken');
            $table->integer('score')->default(0);     
            $table->timestamps();
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
