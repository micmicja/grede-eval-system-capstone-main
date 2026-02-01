



<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleGradesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all students
        $students = DB::table('students')->get();

        foreach ($students as $student) {
            // Get the teacher user_id from the student record
            $teacherId = $student->teacher_id;

            // Add sample grades for each student (low scores to trigger High Risk)
            DB::table('quiz_exam_activity')->insert([
                ['full_name' => $student->full_name, 'subject' => $student->subject, 'section' => $student->section, 'user_id' => $teacherId, 'activity_type' => 'quiz', 'activity_title' => 'Quiz 1', 'date_taken' => now()->subDays(10), 'score' => 65, 'created_at' => now(), 'updated_at' => now()],
                ['full_name' => $student->full_name, 'subject' => $student->subject, 'section' => $student->section, 'user_id' => $teacherId, 'activity_type' => 'quiz', 'activity_title' => 'Quiz 2', 'date_taken' => now()->subDays(8), 'score' => 68, 'created_at' => now(), 'updated_at' => now()],
                ['full_name' => $student->full_name, 'subject' => $student->subject, 'section' => $student->section, 'user_id' => $teacherId, 'activity_type' => 'exam', 'activity_title' => 'Midterm Exam', 'date_taken' => now()->subDays(5), 'score' => 60, 'created_at' => now(), 'updated_at' => now()],
                ['full_name' => $student->full_name, 'subject' => $student->subject, 'section' => $student->section, 'user_id' => $teacherId, 'activity_type' => 'exam', 'activity_title' => 'Final Exam', 'date_taken' => now()->subDays(2), 'score' => 62, 'created_at' => now(), 'updated_at' => now()],
                ['full_name' => $student->full_name, 'subject' => $student->subject, 'section' => $student->section, 'user_id' => $teacherId, 'activity_type' => 'activity', 'activity_title' => 'Activity 1', 'date_taken' => now()->subDays(12), 'score' => 70, 'created_at' => now(), 'updated_at' => now()],
                ['full_name' => $student->full_name, 'subject' => $student->subject, 'section' => $student->section, 'user_id' => $teacherId, 'activity_type' => 'activity', 'activity_title' => 'Activity 2', 'date_taken' => now()->subDays(7), 'score' => 72, 'created_at' => now(), 'updated_at' => now()],
                ['full_name' => $student->full_name, 'subject' => $student->subject, 'section' => $student->section, 'user_id' => $teacherId, 'activity_type' => 'project', 'activity_title' => 'Project 1', 'date_taken' => now()->subDays(15), 'score' => 68, 'created_at' => now(), 'updated_at' => now()],
                ['full_name' => $student->full_name, 'subject' => $student->subject, 'section' => $student->section, 'user_id' => $teacherId, 'activity_type' => 'recitation', 'activity_title' => 'Recitation 1', 'date_taken' => now()->subDays(6), 'score' => 72, 'created_at' => now(), 'updated_at' => now()],
                ['full_name' => $student->full_name, 'subject' => $student->subject, 'section' => $student->section, 'user_id' => $teacherId, 'activity_type' => 'recitation', 'activity_title' => 'Recitation 2', 'date_taken' => now()->subDays(3), 'score' => 75, 'created_at' => now(), 'updated_at' => now()],
            ]);

            // Add attendance records (70% present rate)
            DB::table('attendance')->insert([
                ['student_id' => $student->id, 'status' => 'Present', 'created_at' => now(), 'updated_at' => now()],
                ['student_id' => $student->id, 'status' => 'Present', 'created_at' => now(), 'updated_at' => now()],
                ['student_id' => $student->id, 'status' => 'Present', 'created_at' => now(), 'updated_at' => now()],
                ['student_id' => $student->id, 'status' => 'Present', 'created_at' => now(), 'updated_at' => now()],
                ['student_id' => $student->id, 'status' => 'Present', 'created_at' => now(), 'updated_at' => now()],
                ['student_id' => $student->id, 'status' => 'Present', 'created_at' => now(), 'updated_at' => now()],
                ['student_id' => $student->id, 'status' => 'Present', 'created_at' => now(), 'updated_at' => now()],
                ['student_id' => $student->id, 'status' => 'Absent', 'created_at' => now(), 'updated_at' => now()],
                ['student_id' => $student->id, 'status' => 'Absent', 'created_at' => now(), 'updated_at' => now()],
                ['student_id' => $student->id, 'status' => 'Absent', 'created_at' => now(), 'updated_at' => now()],
            ]);

            $this->command->info("Added sample grades for student: {$student->full_name}");
        }

        $this->command->info("✓ Sample grades and attendance added for all students!");
    }
}
