<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Quiz_exam_activity;
use App\Models\Attendance;
use App\Models\TeacherSetting;
use App\Models\StudentObservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class ExportController extends Controller
{
    /**
     * Export student list to PDF or Excel
     */
    public function exportStudentList(Request $request)
    {
        $teacherId = Auth::id();
        $teacher = Auth::user();
        
        $students = Student::where('teacher_id', $teacherId)->get();
        
        $format = $request->input('format', 'pdf');
        $filename = 'student-list-' . Carbon::now()->format('Y-m-d');
        
        if ($format === 'excel') {
            return $this->generateStudentListCSV($students, $filename);
        }
        
        if ($format === 'word') {
            return $this->generateStudentListWord($students, $teacher, $filename);
        }
        
        // Default to PDF
        $pdf = Pdf::loadView('exports.student-list', [
            'students' => $students,
            'teacher' => $teacher,
            'date' => Carbon::now()->format('F d, Y')
        ]);
        
        return $pdf->download($filename . '.pdf');
    }
    
    /**
     * Export individual student report to PDF or Excel
     */
    public function exportStudentReport(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $teacherId = Auth::id();
        $teacher = Auth::user();
        
        // Get teacher settings
        $settings = TeacherSetting::where('user_id', $teacherId)->first();
        
        // Determine current semester
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $semester = $month <= 6 ? 1 : 2;
        
        if ($semester === 1) {
            $start = Carbon::create($year, 1, 1)->startOfDay();
            $end = Carbon::create($year, 6, 30)->endOfDay();
        } else {
            $start = Carbon::create($year, 7, 1)->startOfDay();
            $end = Carbon::create($year, 12, 31)->endOfDay();
        }
        
        // Get all grades
        $allActivities = Quiz_exam_activity::where('full_name', $student->full_name)
            ->where('user_id', $teacherId)
            ->whereBetween('date_taken', [$start->toDateString(), $end->toDateString()])
            ->get();
        
        $quizzes = $allActivities->where('activity_type', 'Quiz');
        $exams = $allActivities->where('activity_type', 'Exam');
        $activities = $allActivities->where('activity_type', 'Activity');
        $projects = $allActivities->where('activity_type', 'Project');
        $recitations = $allActivities->where('activity_type', 'Recitation');
        
        // Get attendance
        $attendanceRecords = Attendance::where('full_name', $student->full_name)
            ->where('user_id', $teacherId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();
        
        // Calculate averages
        $quizAvg = $quizzes->count() > 0 ? $quizzes->avg('score') : 0;
        $examAvg = $exams->count() > 0 ? $exams->avg('score') : 0;
        $activityAvg = $activities->count() > 0 ? $activities->avg('score') : 0;
        $projectAvg = $projects->count() > 0 ? $projects->avg('score') : 0;
        $recitationAvg = $recitations->count() > 0 ? $recitations->avg('score') : 0;
        
        $totalAttendance = $attendanceRecords->count();
        $presentCount = $attendanceRecords->where('present', true)->count();
        $attendancePercentage = $totalAttendance > 0 ? ($presentCount / $totalAttendance) * 100 : 0;
        
        // Get weights
        $weights = [
            'attendance' => $settings->attendance_weight ?? 10,
            'quiz' => $settings->quiz_weight ?? 15,
            'exam' => $settings->exam_weight ?? 25,
            'activity' => $settings->activity_weight ?? 25,
            'project' => $settings->project_weight ?? 15,
            'recitation' => $settings->recitation_weight ?? 10
        ];
        
        // Calculate overall score
        $numerator = 0;
        $denominator = 0;
        
        if ($totalAttendance > 0) {
            $numerator += ($attendancePercentage * $weights['attendance']);
            $denominator += $weights['attendance'];
        }
        if ($quizzes->count() > 0) {
            $numerator += ($quizAvg * $weights['quiz']);
            $denominator += $weights['quiz'];
        }
        if ($exams->count() > 0) {
            $numerator += ($examAvg * $weights['exam']);
            $denominator += $weights['exam'];
        }
        if ($activities->count() > 0) {
            $numerator += ($activityAvg * $weights['activity']);
            $denominator += $weights['activity'];
        }
        if ($projects->count() > 0) {
            $numerator += ($projectAvg * $weights['project']);
            $denominator += $weights['project'];
        }
        if ($recitations->count() > 0) {
            $numerator += ($recitationAvg * $weights['recitation']);
            $denominator += $weights['recitation'];
        }
        
        $overallScore = $denominator > 0 ? ($numerator / $denominator) : 0;
        
        // Determine risk status based on score
        $riskStatus = 'Low';
        if ($overallScore < 60) {
            $riskStatus = 'High';
        } elseif ($overallScore >= 60 && $overallScore <= 75) {
            $riskStatus = 'Mid High';
        } elseif ($overallScore >= 76 && $overallScore <= 89) {
            $riskStatus = 'Mid';
        }
        
        // Get latest risk assessment for student
        $riskAssessment = StudentObservation::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->first();
        
        $riskBehaviors = [];
        if ($riskAssessment && $riskAssessment->observed_behaviors) {
            $riskBehaviors = is_array($riskAssessment->observed_behaviors) 
                ? $riskAssessment->observed_behaviors 
                : json_decode($riskAssessment->observed_behaviors, true);
        }
        
        $format = $request->input('format', 'pdf');
        $filename = 'student-report-' . $student->full_name . '-' . Carbon::now()->format('Y-m-d');
        
        if ($format === 'excel') {
            return $this->generateStudentReportCSV(
                $student,
                $overallScore,
                $riskStatus,
                $riskBehaviors,
                $quizAvg,
                $examAvg,
                $activityAvg,
                $projectAvg,
                $recitationAvg,
                $attendancePercentage,
                $filename
            );
        }
        
        if ($format === 'word') {
            return $this->generateStudentReportWord(
                $student,
                $overallScore,
                $riskStatus,
                $riskBehaviors,
                $quizAvg,
                $examAvg,
                $activityAvg,
                $projectAvg,
                $recitationAvg,
                $attendancePercentage,
                $filename
            );
        }
        
        // Default to PDF
        $pdf = Pdf::loadView('exports.student-report', [
            'student' => $student,
            'teacher' => $teacher,
            'quizzes' => $quizzes,
            'exams' => $exams,
            'activities' => $activities,
            'projects' => $projects,
            'recitations' => $recitations,
            'attendanceRecords' => $attendanceRecords,
            'quizAvg' => $quizAvg,
            'examAvg' => $examAvg,
            'activityAvg' => $activityAvg,
            'projectAvg' => $projectAvg,
            'recitationAvg' => $recitationAvg,
            'attendancePercentage' => $attendancePercentage,
            'overallScore' => $overallScore,
            'weights' => $weights,
            'riskAssessment' => $riskAssessment,
            'date' => Carbon::now()->format('F d, Y'),
            'semester' => $semester,
            'year' => $year
        ]);
        
        return $pdf->download($filename . '.pdf');
    }
    
    /**
     * Export risk assessment observations to PDF or Excel
     */
    public function exportRiskAssessments(Request $request)
    {
        $teacherId = Auth::id();
        $teacher = Auth::user();
        
        $query = StudentObservation::with('student')
            ->where('teacher_id', $teacherId);
        
        // Filter by specific student if provided
        if ($request->has('student_id') && $request->student_id != '') {
            $query->where('student_id', $request->student_id);
        }
        
        $observations = $query->orderBy('created_at', 'desc')->get();
        
        // Get student name for filename if specific student selected
        $filename = 'risk-assessments';
        $riskLevel = 'All';
        if ($request->has('student_id') && $request->student_id != '') {
            $student = Student::find($request->student_id);
            if ($student) {
                $filename = 'risk-assessment-' . str_replace(' ', '-', strtolower($student->full_name));
            }
        }
        
        $format = $request->input('format', 'pdf');
        $filename .= '-' . Carbon::now()->format('Y-m-d');
        
        if ($format === 'excel') {
            return $this->generateRiskAssessmentsCSV($observations, $filename);
        }
        
        if ($format === 'word') {
            return $this->generateRiskAssessmentsWord($observations, $filename);
        }
        
        // Default to PDF
        $pdf = Pdf::loadView('exports.risk-assessments', [
            'observations' => $observations,
            'teacher' => $teacher,
            'date' => Carbon::now()->format('F d, Y')
        ]);
        
        return $pdf->download($filename . '.pdf');
    }
    
    /**
     * Export quiz/exam/activity results to PDF or Excel
     */
    public function exportActivityResults(Request $request)
    {
        $teacherId = Auth::id();
        $teacher = Auth::user();
        
        $activityType = $request->input('type', 'Quiz'); // Quiz, Exam, Activity, Project, Recitation
        $activityTitle = $request->input('title');
        $dateTaken = $request->input('date');
        
        $results = Quiz_exam_activity::where('user_id', $teacherId)
            ->where('activity_type', $activityType)
            ->where('activity_title', $activityTitle)
            ->where('date_taken', $dateTaken)
            ->where('full_name', '!=', '__PLACEHOLDER__')
            ->whereNotNull('full_name')
            ->orderBy('score', 'desc')
            ->get();
        
        $format = $request->input('format', 'pdf');
        $filename = strtolower($activityType) . '-results-' . str_replace(' ', '-', strtolower($activityTitle)) . '-' . Carbon::now()->format('Y-m-d');
        
        if ($format === 'excel') {
            return $this->generateActivityResultsCSV($results, $activityType, $activityTitle, $filename);
        }
        
        if ($format === 'word') {
            return $this->generateActivityResultsWord($results, $activityType, $activityTitle, $filename);
        }
        
        // Default to PDF
        $pdf = Pdf::loadView('exports.activity-results', [
            'results' => $results,
            'teacher' => $teacher,
            'activityType' => $activityType,
            'activityTitle' => $activityTitle,
            'dateTaken' => $dateTaken,
            'date' => Carbon::now()->format('F d, Y')
        ]);
        
        return $pdf->download($filename . '.pdf');
    }
    
    /**
     * Generate CSV for Student List
     */
    private function generateStudentListCSV($students, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];
        
        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            
            // Title
            fputcsv($file, ['STUDENT LIST']);
            fputcsv($file, ['Generated on: ' . Carbon::now()->format('F d, Y')]);
            fputcsv($file, []);
            
            // Add headers
            fputcsv($file, ['No.', 'Student ID', 'Student Name', 'Section', 'Subject']);
            
            // Add data
            foreach ($students as $index => $student) {
                fputcsv($file, [
                    $index + 1,
                    $student->student_id ?? 'N/A',
                    $student->full_name,
                    $student->section,
                    $student->subject ?? 'N/A'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Generate CSV for Student Report
     */
    private function generateStudentReportCSV($student, $overallScore, $riskStatus, $riskBehaviors, $quizAvg, $examAvg, $activityAvg, $projectAvg, $recitationAvg, $attendancePercentage, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];
        
        $callback = function() use ($student, $overallScore, $riskStatus, $riskBehaviors, $quizAvg, $examAvg, $activityAvg, $projectAvg, $recitationAvg, $attendancePercentage) {
            $file = fopen('php://output', 'w');
            
            // Title
            fputcsv($file, ['STUDENT ACADEMIC REPORT']);
            fputcsv($file, []);
            
            // Simplified two-column format
            fputcsv($file, ['STUDENT INFORMATION', '']);
            fputcsv($file, ['Student ID', $student->student_id ?? 'N/A']);
            fputcsv($file, ['Name', $student->full_name]);
            fputcsv($file, ['Section', $student->section]);
            fputcsv($file, ['Subject', $student->subject ?? 'N/A']);
            fputcsv($file, []);
            
            // Overall Score
            fputcsv($file, ['OVERALL ACADEMIC SCORE', number_format($overallScore, 2) . '%']);
            fputcsv($file, []);
            
            // Risk Assessment (if not Low)
            if ($riskStatus !== 'Low') {
                fputcsv($file, ['RISK ASSESSMENT', '']);
                fputcsv($file, ['Risk Level', $riskStatus]);
                if (!empty($riskBehaviors)) {
                    foreach ($riskBehaviors as $behavior) {
                        fputcsv($file, ['Behavior', $behavior]);
                    }
                }
                fputcsv($file, []);
            }
            
            // Grade Breakdown
            fputcsv($file, ['GRADE BREAKDOWN', '']);
            fputcsv($file, ['Component', 'Average Score (%)']);
            fputcsv($file, ['Quiz', number_format($quizAvg, 2)]);
            fputcsv($file, ['Exam', number_format($examAvg, 2)]);
            fputcsv($file, ['Activity', number_format($activityAvg, 2)]);
            fputcsv($file, ['Project', number_format($projectAvg, 2)]);
            fputcsv($file, ['Recitation', number_format($recitationAvg, 2)]);
            fputcsv($file, ['Attendance', number_format($attendancePercentage, 2)]);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Generate CSV for Risk Assessments
     */
    private function generateRiskAssessmentsCSV($observations, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];
        
        $callback = function() use ($observations) {
            $file = fopen('php://output', 'w');
            
            // Title
            fputcsv($file, ['STUDENT RISK ASSESSMENT REPORT']);
            fputcsv($file, ['Generated on: ' . Carbon::now()->format('F d, Y')]);
            fputcsv($file, []);
            
            // Add headers
            fputcsv($file, ['No.', 'Student ID', 'Student Name', 'Academic Score', 'Risk Level', 'Observed Behaviors', 'Date Assessed']);
            
            // Add data
            foreach ($observations as $index => $observation) {
                $student = $observation->student;
                $behaviors = is_array($observation->observed_behaviors) 
                    ? implode('; ', $observation->observed_behaviors) 
                    : $observation->observed_behaviors;
                    
                fputcsv($file, [
                    $index + 1,
                    $student->student_id ?? 'N/A',
                    $student->full_name ?? 'N/A',
                    number_format($observation->overall_score, 2) . '%',
                    $observation->risk_status,
                    $behaviors ?? 'None',
                    $observation->created_at ? $observation->created_at->format('Y-m-d') : 'N/A'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Generate CSV for Activity Results
     */
    private function generateActivityResultsCSV($results, $activityType, $activityTitle, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];
        
        $callback = function() use ($results, $activityType, $activityTitle) {
            $file = fopen('php://output', 'w');
            
            // Title
            fputcsv($file, [strtoupper($activityType) . ' RESULTS']);
            fputcsv($file, ['Activity Title', $activityTitle]);
            fputcsv($file, []);
            
            // Statistics
            if ($results->count() > 0) {
                $totalScore = $results->first()->total_score ?? 100;
                $passingScore = $totalScore * 0.75;
                
                fputcsv($file, ['STATISTICS', '']);
                fputcsv($file, ['Highest Score', $results->max('score') ?? 0]);
                fputcsv($file, ['Lowest Score', $results->min('score') ?? 0]);
                fputcsv($file, ['Average Score', number_format($results->avg('score'), 2)]);
                fputcsv($file, ['Total Students', $results->count()]);
                fputcsv($file, ['Passed', $results->filter(fn($r) => $r->score >= $passingScore)->count()]);
                fputcsv($file, ['Failed', $results->filter(fn($r) => $r->score < $passingScore)->count()]);
                fputcsv($file, []);
            }
            
            // Student Results Table
            fputcsv($file, ['STUDENT RESULTS', '', '', '', '']);
            fputcsv($file, ['No.', 'Student ID', 'Student Name', 'Score', 'Total Score', 'Status']);
            
            // Results
            foreach ($results as $index => $result) {
                $totalScore = $result->total_score ?? 100;
                $passingScore = $totalScore * 0.75;
                $status = $result->score >= $passingScore ? 'Passed' : 'Failed';
                
                fputcsv($file, [
                    $index + 1,
                    $result->student_id ?? 'N/A',
                    $result->full_name,
                    $result->score,
                    $result->total_score,
                    $status
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    // Word generation methods
    private function generateStudentListWord($students)
    {
        $phpWord = new PhpWord();
        
        // Add section
        $section = $phpWord->addSection();
        
        // Add title
        $section->addText(
            'STUDENT LIST',
            ['bold' => true, 'size' => 16],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        $section->addTextBreak(1);
        
        // Create table
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'width' => 100 * 50,
            'unit' => \PhpOffice\PhpWord\Style\Table::WIDTH_PERCENT
        ]);
        
        // Header row
        $table->addRow(500);
        $table->addCell(1500, ['bgColor' => 'CCCCCC'])->addText('No.', ['bold' => true]);
        $table->addCell(3000, ['bgColor' => 'CCCCCC'])->addText('Student ID', ['bold' => true]);
        $table->addCell(4000, ['bgColor' => 'CCCCCC'])->addText('Name', ['bold' => true]);
        $table->addCell(3000, ['bgColor' => 'CCCCCC'])->addText('Section', ['bold' => true]);
        
        // Data rows
        foreach ($students as $index => $student) {
            $table->addRow();
            $table->addCell(1500)->addText($index + 1);
            $table->addCell(3000)->addText($student->student_id ?? 'N/A');
            $table->addCell(4000)->addText($student->full_name);
            $table->addCell(3000)->addText($student->section);
        }
        
        // Save to temporary file
        $fileName = 'student_list_' . now()->format('Y-m-d_His') . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'word_');
        
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);
        
        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    private function generateStudentReportWord($student, $overallScore, $riskStatus, $riskBehaviors, $quizAvg, $examAvg, $activityAvg, $projectAvg, $recitationAvg, $attendancePercentage)
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        
        // Title
        $section->addText(
            'STUDENT ACADEMIC REPORT',
            ['bold' => true, 'size' => 16],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        $section->addTextBreak(2);
        
        // Student Information
        $section->addText('STUDENT INFORMATION', ['bold' => true, 'size' => 14, 'underline' => 'single']);
        $section->addTextBreak(1);
        
        $infoTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        $infoTable->addRow();
        $infoTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Student ID', ['bold' => true]);
        $infoTable->addCell(6000)->addText($student->student_id ?? 'N/A');
        $infoTable->addRow();
        $infoTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Name', ['bold' => true]);
        $infoTable->addCell(6000)->addText($student->full_name);
        $infoTable->addRow();
        $infoTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Section', ['bold' => true]);
        $infoTable->addCell(6000)->addText($student->section);
        $infoTable->addRow();
        $infoTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Subject', ['bold' => true]);
        $infoTable->addCell(6000)->addText($student->subject ?? 'N/A');
        
        $section->addTextBreak(2);
        
        // Academic Performance
        $section->addText('ACADEMIC PERFORMANCE', ['bold' => true, 'size' => 14, 'underline' => 'single']);
        $section->addTextBreak(1);
        
        $perfTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        $perfTable->addRow();
        $perfTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Quiz Average', ['bold' => true]);
        $perfTable->addCell(6000)->addText(number_format($quizAvg, 2) . '%');
        $perfTable->addRow();
        $perfTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Exam Average', ['bold' => true]);
        $perfTable->addCell(6000)->addText(number_format($examAvg, 2) . '%');
        $perfTable->addRow();
        $perfTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Activity Average', ['bold' => true]);
        $perfTable->addCell(6000)->addText(number_format($activityAvg, 2) . '%');
        $perfTable->addRow();
        $perfTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Project Average', ['bold' => true]);
        $perfTable->addCell(6000)->addText(number_format($projectAvg, 2) . '%');
        $perfTable->addRow();
        $perfTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Recitation Average', ['bold' => true]);
        $perfTable->addCell(6000)->addText(number_format($recitationAvg, 2) . '%');
        $perfTable->addRow();
        $perfTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Overall Performance', ['bold' => true]);
        $perfTable->addCell(6000)->addText(number_format($overallScore, 2) . '%');
        
        $section->addTextBreak(2);
        
        // Behavioral Assessment
        $section->addText('BEHAVIORAL ASSESSMENT', ['bold' => true, 'size' => 14, 'underline' => 'single']);
        $section->addTextBreak(1);
        
        $behavTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        $behavTable->addRow();
        $behavTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Attendance Rate', ['bold' => true]);
        $behavTable->addCell(6000)->addText(number_format($attendancePercentage, 2) . '%');
        $behavTable->addRow();
        $behavTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Risk Status', ['bold' => true]);
        $textRun = $behavTable->addCell(6000)->addTextRun();
        $color = $riskStatus === 'High Risk' ? 'FF0000' : ($riskStatus === 'At Risk' ? 'FFA500' : '00AA00');
        $textRun->addText($riskStatus, ['bold' => true, 'color' => $color]);
        
        if ($riskBehaviors) {
            $behavTable->addRow();
            $behavTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Risk Behaviors', ['bold' => true]);
            $behavTable->addCell(6000)->addText($riskBehaviors);
        }
        
        // Save
        $fileName = 'student_report_' . ($student->student_id ?? 'unknown') . '_' . now()->format('Y-m-d_His') . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'word_');
        
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);
        
        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    private function generateRiskAssessmentsWord($assessments)
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        
        // Title
        $section->addText(
            'RISK ASSESSMENT REPORT',
            ['bold' => true, 'size' => 16],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        $section->addTextBreak(1);
        
        $section->addText(
            'Generated: ' . now()->format('F d, Y h:i A'),
            ['size' => 10],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        $section->addTextBreak(2);
        
        // Create table
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'width' => 100 * 50,
            'unit' => \PhpOffice\PhpWord\Style\Table::WIDTH_PERCENT
        ]);
        
        // Header row
        $table->addRow(500);
        $table->addCell(1500, ['bgColor' => 'CCCCCC'])->addText('No.', ['bold' => true]);
        $table->addCell(2500, ['bgColor' => 'CCCCCC'])->addText('Student ID', ['bold' => true]);
        $table->addCell(3500, ['bgColor' => 'CCCCCC'])->addText('Name', ['bold' => true]);
        $table->addCell(2000, ['bgColor' => 'CCCCCC'])->addText('Section', ['bold' => true]);
        $table->addCell(2000, ['bgColor' => 'CCCCCC'])->addText('Risk Status', ['bold' => true]);
        $table->addCell(4000, ['bgColor' => 'CCCCCC'])->addText('Risk Behaviors', ['bold' => true]);
        
        // Data rows
        foreach ($assessments as $index => $assessment) {
            $table->addRow();
            $table->addCell(1500)->addText($index + 1);
            $table->addCell(2500)->addText($assessment->student_id ?? 'N/A');
            $table->addCell(3500)->addText($assessment->full_name);
            $table->addCell(2000)->addText($assessment->section);
            
            // Risk status with color
            $color = $assessment->risk_status === 'High Risk' ? 'FF0000' : 
                     ($assessment->risk_status === 'At Risk' ? 'FFA500' : '00AA00');
            $table->addCell(2000)->addText($assessment->risk_status, ['bold' => true, 'color' => $color]);
            
            // Risk behaviors
            $behaviors = $assessment->risk_behaviors ?? 'None';
            $table->addCell(4000)->addText($behaviors);
        }
        
        // Save
        $fileName = 'risk_assessments_' . now()->format('Y-m-d_His') . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'word_');
        
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);
        
        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    private function generateActivityResultsWord($activity, $results, $stats)
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        
        // Title
        $section->addText(
            'ACTIVITY RESULTS REPORT',
            ['bold' => true, 'size' => 16],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        $section->addTextBreak(2);
        
        // Activity Information
        $section->addText('ACTIVITY INFORMATION', ['bold' => true, 'size' => 14, 'underline' => 'single']);
        $section->addTextBreak(1);
        
        $infoTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        $infoTable->addRow();
        $infoTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Title', ['bold' => true]);
        $infoTable->addCell(6000)->addText($activity->title);
        $infoTable->addRow();
        $infoTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Type', ['bold' => true]);
        $infoTable->addCell(6000)->addText(ucfirst($activity->type));
        $infoTable->addRow();
        $infoTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Total Score', ['bold' => true]);
        $infoTable->addCell(6000)->addText($activity->total_score);
        
        $section->addTextBreak(2);
        
        // Statistics
        $section->addText('STATISTICS', ['bold' => true, 'size' => 14, 'underline' => 'single']);
        $section->addTextBreak(1);
        
        $statsTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        $statsTable->addRow();
        $statsTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Total Students', ['bold' => true]);
        $statsTable->addCell(6000)->addText($stats['total_students']);
        $statsTable->addRow();
        $statsTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Average Score', ['bold' => true]);
        $statsTable->addCell(6000)->addText(number_format($stats['average_score'], 2));
        $statsTable->addRow();
        $statsTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Highest Score', ['bold' => true]);
        $statsTable->addCell(6000)->addText($stats['highest_score']);
        $statsTable->addRow();
        $statsTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Lowest Score', ['bold' => true]);
        $statsTable->addCell(6000)->addText($stats['lowest_score']);
        $statsTable->addRow();
        $statsTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Passed', ['bold' => true]);
        $statsTable->addCell(6000)->addText($stats['passed']);
        $statsTable->addRow();
        $statsTable->addCell(3000, ['bgColor' => 'E7E6E6'])->addText('Failed', ['bold' => true]);
        $statsTable->addCell(6000)->addText($stats['failed']);
        
        $section->addTextBreak(2);
        
        // Results Table
        $section->addText('STUDENT RESULTS', ['bold' => true, 'size' => 14, 'underline' => 'single']);
        $section->addTextBreak(1);
        
        $resultsTable = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'width' => 100 * 50,
            'unit' => \PhpOffice\PhpWord\Style\Table::WIDTH_PERCENT
        ]);
        
        // Header row
        $resultsTable->addRow(500);
        $resultsTable->addCell(1500, ['bgColor' => 'CCCCCC'])->addText('No.', ['bold' => true]);
        $resultsTable->addCell(2500, ['bgColor' => 'CCCCCC'])->addText('Student ID', ['bold' => true]);
        $resultsTable->addCell(3500, ['bgColor' => 'CCCCCC'])->addText('Name', ['bold' => true]);
        $resultsTable->addCell(2000, ['bgColor' => 'CCCCCC'])->addText('Score', ['bold' => true]);
        $resultsTable->addCell(2000, ['bgColor' => 'CCCCCC'])->addText('Total', ['bold' => true]);
        $resultsTable->addCell(2000, ['bgColor' => 'CCCCCC'])->addText('Status', ['bold' => true]);
        
        // Data rows
        foreach ($results as $index => $result) {
            $totalScore = $result->total_score ?? 100;
            $passingScore = $totalScore * 0.75;
            $status = $result->score >= $passingScore ? 'Passed' : 'Failed';
            $statusColor = $status === 'Passed' ? '00AA00' : 'FF0000';
            
            $resultsTable->addRow();
            $resultsTable->addCell(1500)->addText($index + 1);
            $resultsTable->addCell(2500)->addText($result->student_id ?? 'N/A');
            $resultsTable->addCell(3500)->addText($result->full_name);
            $resultsTable->addCell(2000)->addText($result->score);
            $resultsTable->addCell(2000)->addText($result->total_score);
            $resultsTable->addCell(2000)->addText($status, ['bold' => true, 'color' => $statusColor]);
        }
        
        // Save
        $fileName = 'activity_results_' . str_replace(' ', '_', $activity->title) . '_' . now()->format('Y-m-d_His') . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'word_');
        
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);
        
        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}