<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Quiz_exam_activity;
use App\Models\Student;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all non-placeholder records
        $records = Quiz_exam_activity::where('full_name', '!=', '__PLACEHOLDER__')
            ->whereNull('student_id')
            ->get();

        foreach ($records as $record) {
            $found = false;
            $oldName = strtolower(trim($record->full_name));
            
            // Get all students for this teacher
            $allStudents = Student::where('teacher_id', $record->user_id)->get();
            
            foreach ($allStudents as $student) {
                $lastNameLower = strtolower($student->last_name ?? '');
                $firstNameLower = strtolower($student->first_name ?? '');
                $middleNameLower = strtolower($student->middle_name ?? '');
                
                // Try exact match with formatted name
                $formattedLower = strtolower(trim($lastNameLower . ' ' . $firstNameLower . ' ' . $middleNameLower));
                if ($formattedLower === $oldName && !empty(trim($formattedLower))) {
                    // Update with student_id and formatted name
                    $formattedName = trim(($student->last_name ?? 'N/A') . ' ' . ($student->first_name ?? 'N/A') . ' ' . ($student->middle_name ?? ''));
                    $record->update([
                        'full_name' => $formattedName,
                        'student_id' => $student->id,
                    ]);
                    $found = true;
                    break;
                }
                
                // Try matching if all three name parts appear in old name
                if (!$found && !empty($lastNameLower) && strpos($oldName, $lastNameLower) !== false &&
                    !empty($firstNameLower) && strpos($oldName, $firstNameLower) !== false) {
                    $formattedName = trim(($student->last_name ?? 'N/A') . ' ' . ($student->first_name ?? 'N/A') . ' ' . ($student->middle_name ?? ''));
                    $record->update([
                        'full_name' => $formattedName,
                        'student_id' => $student->id,
                    ]);
                    $found = true;
                    break;
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed - data migration
    }
};

