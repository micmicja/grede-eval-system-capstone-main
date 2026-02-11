<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\TeacherSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class TeacherController extends Controller
{
    //

    // teacher dashboard
    public function index()
    {
        // Kukunin natin ang mga estudyante kasama ang kanilang pinakabagong counseling referral
        $students = Student::where('teacher_id', Auth::id())
            ->with(['evaluations' => function ($query) {
                // Kunin lang ang mga pending o ongoing at may schedule na
                $query->whereNotNull('scheduled_at')
                    ->where('status', '!=', 'resolved')
                    ->orderBy('scheduled_at', 'asc');
            }])
            ->with(['observations' => function ($query) {
                // Check if student has been referred to councilor
                $query->where('referred_to_councilor', true);
            }])
            ->get();

        $percentage = TeacherSetting::where('user_id', Auth::id())->first();

        // Calculate overallWeighted for each student (same as StudentReport)
        foreach ($students as $student) {
            // Get all grades for this student
            $quizzes = \App\Models\Quiz_exam_activity::where('student_id', $student->id)
                ->where('type', 'Quiz')->get();
            $exams = \App\Models\Quiz_exam_activity::where('student_id', $student->id)
                ->where('type', 'Exam')->get();
            $activities = \App\Models\Quiz_exam_activity::where('student_id', $student->id)
                ->where('type', 'Activity')->get();
            $projects = \App\Models\Quiz_exam_activity::where('student_id', $student->id)
                ->where('type', 'Project')->get();
            $recitations = \App\Models\Quiz_exam_activity::where('student_id', $student->id)
                ->where('type', 'Recitation')->get();
            $attendances = \App\Models\Attendance::where('student_id', $student->id)->get();

            // Calculate averages
            $quizAvg = $quizzes->count() > 0 ? $quizzes->avg('weighted_score') : 0;
            $examAvg = $exams->count() > 0 ? $exams->avg('weighted_score') : 0;
            $activityAvg = $activities->count() > 0 ? $activities->avg('weighted_score') : 0;
            $projectAvg = $projects->count() > 0 ? $projects->avg('weighted_score') : 0;
            $recitationAvg = $recitations->count() > 0 ? $recitations->avg('weighted_score') : 0;
            $attendanceAvg = $attendances->count() > 0 ? ($attendances->where('status', 'present')->count() / $attendances->count()) * 100 : 0;

            // Calculate weighted overall score
            $quizWeight = $percentage->quiz_weight ?? 25;
            $examWeight = $percentage->exam_weight ?? 25;
            $activityWeight = $percentage->activity_weight ?? 25;
            $projectWeight = $percentage->project_weight ?? 15;
            $recitationWeight = $percentage->recitation_weight ?? 5;
            $attendanceWeight = $percentage->attendance_weight ?? 5;

            $student->overallWeighted = ($quizAvg * $quizWeight / 100) +
                                       ($examAvg * $examWeight / 100) +
                                       ($activityAvg * $activityWeight / 100) +
                                       ($projectAvg * $projectWeight / 100) +
                                       ($recitationAvg * $recitationWeight / 100) +
                                       ($attendanceAvg * $attendanceWeight / 100);
        }

        return view('Teacher.Dashboard', compact('students', 'percentage'));
    }






    public function addStudentForm()
    {
        //
        return view('Teacher.AddStudent');
    }

    //  create teacgeher dashboard
    public function store(Request $request)
    {
        // validate the request
        $validatedData = $request->validate([
            'fullname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'section' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ]);


        // create new teacher

        User::create([
            'full_name' => $validatedData['fullname'],
            'username' => $validatedData['username'],
            'section' => $validatedData['section'],
            'subject' => $validatedData['subject'],
            'role' => 'teacher',
            'password' => bcrypt($validatedData['password']),
        ]);

        return redirect()->route('Dashboard.teacher')->with('success', 'Teacher account created successfully.');
    }

    // Get student overall score for observation modal
    public function getStudentOverallScore($studentId)
    {
        $student = Student::findOrFail($studentId);
        $settings = TeacherSetting::where('user_id', Auth::id())->first();

        // Get all records for this student (same logic as StudentReportController)
        $commonQuery = [
            ['full_name', '=', $student->full_name],
            ['user_id', '=', Auth::id()]
        ];

        $attendanceRecords = \App\Models\Attendance::where($commonQuery)->get();
        $allActivities = \App\Models\Quiz_exam_activity::where($commonQuery)->get();

        // Group by activity_type (capitalized)
        $quizRecords = $allActivities->where('activity_type', 'Quiz');
        $examRecords = $allActivities->where('activity_type', 'Exam');
        $activityRecords = $allActivities->where('activity_type', 'Activity');
        $projectRecords = $allActivities->where('activity_type', 'Project');
        $recitationRecords = $allActivities->where('activity_type', 'Recitation');

        // Calculate stats
        $totalAttendanceDays = $attendanceRecords->count();
        $presentDays = $attendanceRecords->where('present', true)->count();
        $attendancePercentage = $totalAttendanceDays > 0 ? ($presentDays / $totalAttendanceDays) * 100 : 0;

        $totalQuizzes = $quizRecords->count();
        $averageQuizScore = $totalQuizzes > 0 ? $quizRecords->avg('score') : 0;

        $totalExams = $examRecords->count();
        $averageExamScore = $totalExams > 0 ? $examRecords->avg('score') : 0;

        $totalActivities = $activityRecords->count();
        $averageActivityScore = $totalActivities > 0 ? $activityRecords->avg('score') : 0;

        $totalProjects = $projectRecords->count();
        $averageProjectScore = $totalProjects > 0 ? $projectRecords->avg('score') : 0;

        $totalRecitations = $recitationRecords->count();
        $averageRecitationScore = $totalRecitations > 0 ? $recitationRecords->avg('score') : 0;

        // Weights
        $weights = [
            'attendance' => $settings->attendance_weight ?? 10,
            'quiz'       => $settings->quiz_weight ?? 15,
            'exam'       => $settings->exam_weight ?? 25,
            'activity'   => $settings->activity_weight ?? 25,
            'project'    => $settings->project_weight ?? 15,
            'recitation' => $settings->recitation_weight ?? 10
        ];

        $numerator = 0;
        $denominator = 0;

        // Computation Logic (same as StudentReportController)
        if ($totalAttendanceDays > 0) {
            $numerator += ($attendancePercentage * $weights['attendance']);
            $denominator += $weights['attendance'];
        }
        if ($totalQuizzes > 0) {
            $numerator += ($averageQuizScore * $weights['quiz']);
            $denominator += $weights['quiz'];
        }
        if ($totalExams > 0) {
            $numerator += ($averageExamScore * $weights['exam']);
            $denominator += $weights['exam'];
        }
        if ($totalActivities > 0) {
            $numerator += ($averageActivityScore * $weights['activity']);
            $denominator += $weights['activity'];
        }
        if ($totalProjects > 0) {
            $numerator += ($averageProjectScore * $weights['project']);
            $denominator += $weights['project'];
        }
        if ($totalRecitations > 0) {
            $numerator += ($averageRecitationScore * $weights['recitation']);
            $denominator += $weights['recitation'];
        }

        $overallWeighted = $denominator > 0 ? ($numerator / $denominator) : 0;

        return response()->json(['overall_score' => $overallWeighted]);
    }



    //  CREATE STUDENT FUNCTION
    public function storeStudent(Request $request)
    {
        // validate the request
        $validatedData = $request->validate([
            'student_id' => 'required|string|max:255|unique:students,student_id',
            'full_name' => 'required|string|max:255',
            'section' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
        ]);

        //  get the id of the currently authenticated teacher
        $teacherId = Auth::user()->id;
        // create new student
        Student::create([
            'student_id' => $validatedData['student_id'],
            'full_name' => $validatedData['full_name'],
            'section' => $validatedData['section'],
            'subject' => $validatedData['subject'],
            'teacher_id' => $teacherId,
        ]);

        return redirect()->route('Dashboard.teacher')->with('success', 'Student added successfully.');
    }



    //  Delete Student Function
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();
        return redirect()->route('Dashboard.teacher')->with('success', 'Student deleted successfully.');
    }

    // Show create quiz page
    public function createQuizPage()
    {
        $teacherId = Auth::id();
        
        // Get recent quizzes (unique combinations of activity_title and date_taken)
        // Get total_score from any record (including placeholders)
        $recentQuizzes = \App\Models\Quiz_exam_activity::where('user_id', $teacherId)
            ->where('activity_type', 'Quiz')
            ->select('activity_title', 'date_taken', DB::raw('MAX(total_score) as total_score'))
            ->groupBy('activity_title', 'date_taken')
            ->orderBy('date_taken', 'desc')
            ->take(10)
            ->get();

        return view('Quiz.CreateQuiz', compact('recentQuizzes'));
    }

    // Create new quiz
    public function createQuiz(Request $request)
    {
        $validated = $request->validate([
            'quiz_name' => 'required|string|max:255',
            'date_taken' => 'required|date',
            'total_items' => 'required|integer|min:1',
        ]);

        // Create a placeholder record to store the quiz metadata
        // This ensures total_score is saved even before any student scores are entered
        $teacherId = Auth::id();
        $existingQuiz = \App\Models\Quiz_exam_activity::where('user_id', $teacherId)
            ->where('activity_title', $validated['quiz_name'])
            ->where('date_taken', $validated['date_taken'])
            ->where('activity_type', 'Quiz')
            ->first();

        if (!$existingQuiz) {
            // Create a placeholder record with just the quiz metadata
            \App\Models\Quiz_exam_activity::create([
                'user_id' => $teacherId,
                'activity_type' => 'Quiz',
                'activity_title' => $validated['quiz_name'],
                'date_taken' => $validated['date_taken'],
                'total_score' => $validated['total_items'],
                'full_name' => '__PLACEHOLDER__', // Temporary marker
                'subject' => 'N/A',
                'section' => 'N/A',
                'score' => 0,
                'weighted_score' => 0,
            ]);
        }

        // Redirect to add scores page with quiz details
        return redirect()->route('quiz.add-scores', [
            'quiz_name' => $validated['quiz_name'],
            'date_taken' => $validated['date_taken'],
            'total_items' => $validated['total_items'],
        ]);
    }

    // Show add scores page
    public function addScores(Request $request)
    {
        $quizName = $request->quiz_name;
        $dateTaken = $request->date_taken;
        $totalItems = $request->total_items;
        $selectedSection = $request->section;

        $teacherId = Auth::id();

        // If total_items is not in the request, try to get it from existing records
        if (!$totalItems) {
            $existingRecord = \App\Models\Quiz_exam_activity::where('user_id', $teacherId)
                ->where('activity_title', $quizName)
                ->where('date_taken', $dateTaken)
                ->where('activity_type', 'Quiz')
                ->whereNotNull('total_score')
                ->first();
            
            if ($existingRecord && $existingRecord->total_score) {
                $totalItems = $existingRecord->total_score;
                    Log::info("Retrieved total_items from database: {$totalItems}");
            } else {
                // If still can't find it, require user to provide it
                return redirect()->route('quiz.create-quiz-page')
                    ->with('error', "Please create the quiz '{$quizName}' with a total score first.");
            }
        }

        // Get all distinct sections for this teacher
        $sections = Student::where('teacher_id', $teacherId)
            ->distinct()
            ->orderBy('section')
            ->pluck('section');

        // Get students filtered by section if selected
        $studentsQuery = Student::where('teacher_id', $teacherId);
        
        if ($selectedSection) {
            $studentsQuery->where('section', $selectedSection);
        }
        
        $students = $studentsQuery->orderBy('section')
            ->orderBy('full_name')
            ->get();

        // Get existing scores for this quiz (exclude placeholder)
        $existingScoresData = \App\Models\Quiz_exam_activity::where('user_id', $teacherId)
            ->where('activity_title', $quizName)
            ->where('date_taken', $dateTaken)
            ->where('activity_type', 'Quiz')
            ->where('full_name', '!=', '__PLACEHOLDER__')
            ->get();

        // Convert percentage back to raw score for display
        $existingScores = [];
        foreach ($existingScoresData as $record) {
            // score field contains percentage, convert back to raw: (percentage / 100) * total_items
            $rawScore = round(($record->score / 100) * $totalItems);
            $existingScores[$record->full_name] = $rawScore;
        }

        return view('Quiz.AddScores', compact('students', 'quizName', 'dateTaken', 'totalItems', 'existingScores', 'sections', 'selectedSection'));
    }

    // Save all scores
    public function saveScores(Request $request)
    {
        Log::info('saveScores called', [
            'quiz_name' => $request->quiz_name,
            'date_taken' => $request->date_taken,
            'total_items' => $request->total_items,
            'scores_count' => is_array($request->scores) ? count($request->scores) : 0
        ]);

        $validated = $request->validate([
            'quiz_name' => 'required|string',
            'date_taken' => 'required|date',
            'total_items' => 'required|integer',
            'scores' => 'required|array',
            'scores.*' => 'nullable|integer|min:0',
        ]);

        Log::info('Validation passed');

        $teacherId = Auth::id();
        $percentage = TeacherSetting::where('user_id', $teacherId)->first();

        $savedCount = 0;
        foreach ($validated['scores'] as $fullName => $score) {
            // Skip if no score entered
            if ($score === null || $score === '') {
                continue;
            }

            // Find student by full_name and teacher_id
            $student = Student::where('teacher_id', $teacherId)
                ->where('full_name', $fullName)
                ->first();

            if (!$student) {
                Log::warning("Student not found: {$fullName}");
                continue;
            }

            // Calculate weighted score (percentage)
            $weightedScore = ($score / $validated['total_items']) * 100;
            
            // Ensure total_items is integer
            $totalItems = (int) $validated['total_items'];
            
            Log::info("About to save: total_items type = " . gettype($totalItems) . ", value = " . $totalItems);

            // Update or create the quiz record
            $record = \App\Models\Quiz_exam_activity::updateOrCreate(
                [
                    'user_id' => $teacherId,
                    'full_name' => $fullName,
                    'activity_title' => $validated['quiz_name'],
                    'date_taken' => $validated['date_taken'],
                    'activity_type' => 'Quiz',
                ],
                [
                    'subject' => $student->subject,
                    'section' => $student->section,
                    'score' => $weightedScore,
                    'total_score' => $totalItems,
                    'weighted_score' => $weightedScore,
                ]
            );
            
            Log::info("Saved quiz score for {$fullName}: {$score}/{$validated['total_items']} = {$weightedScore}%", [
                'record_id' => $record->id,
                'was_recently_created' => $record->wasRecentlyCreated
            ]);
            $savedCount++;
        }

        $redirectParams = [
            'quiz_name' => $validated['quiz_name'],
            'date_taken' => $validated['date_taken'],
            'total_items' => $validated['total_items'],
        ];
        
        if ($request->has('selected_section') && $request->selected_section) {
            $redirectParams['section'] = $request->selected_section;
        }
        
        return redirect()->route('quiz.add-scores', $redirectParams)
            ->with('success', "Successfully saved scores for {$savedCount} students!");
    }

    // ==================== EXAM METHODS ====================
    
    public function createExamPage()
    {
        $teacherId = Auth::id();
        $recentExams = \App\Models\Quiz_exam_activity::where('user_id', $teacherId)
            ->where('activity_type', 'Exam')
            ->select('activity_title', 'date_taken', DB::raw('MAX(total_score) as total_score'))
            ->groupBy('activity_title', 'date_taken')
            ->orderBy('date_taken', 'desc')
            ->take(10)
            ->get();
        return view('Exam.CreateExam', compact('recentExams'));
    }

    public function createExam(Request $request)
    {
        $validated = $request->validate([
            'exam_name' => 'required|string|max:255',
            'date_taken' => 'required|date',
            'total_items' => 'required|integer|min:1',
        ]);
        return redirect()->route('exam.add-scores', [
            'exam_name' => $validated['exam_name'],
            'date_taken' => $validated['date_taken'],
            'total_items' => $validated['total_items'],
        ]);
    }

    public function addExamScores(Request $request)
    {
        $examName = $request->exam_name;
        $dateTaken = $request->date_taken;
        $totalItems = $request->total_items;
        $selectedSection = $request->section;
        $teacherId = Auth::id();

        $sections = Student::where('teacher_id', $teacherId)->distinct()->orderBy('section')->pluck('section');
        $studentsQuery = Student::where('teacher_id', $teacherId);
        if ($selectedSection) {
            $studentsQuery->where('section', $selectedSection);
        }
        $students = $studentsQuery->orderBy('section')->orderBy('full_name')->get();

        $existingScoresData = \App\Models\Quiz_exam_activity::where('user_id', $teacherId)
            ->where('activity_title', $examName)
            ->where('date_taken', $dateTaken)
            ->where('activity_type', 'Exam')
            ->get();

        // Convert percentage back to raw score for display
        $existingScores = [];
        foreach ($existingScoresData as $record) {
            $rawScore = round(($record->score / 100) * $totalItems);
            $existingScores[$record->full_name] = $rawScore;
        }

        return view('Exam.AddExamScores', compact('students', 'examName', 'dateTaken', 'totalItems', 'existingScores', 'sections', 'selectedSection'));
    }

    public function saveExamScores(Request $request)
    {
        $validated = $request->validate([
            'exam_name' => 'required|string',
            'date_taken' => 'required|date',
            'total_items' => 'required|integer',
            'scores' => 'required|array',
            'scores.*' => 'nullable|integer|min:0',
        ]);

        $teacherId = Auth::id();
        foreach ($validated['scores'] as $fullName => $score) {
            if ($score === null || $score === '') {
                continue;
            }
            $student = Student::where('teacher_id', $teacherId)->where('full_name', $fullName)->first();
            if (!$student) {
                continue;
            }
            $weightedScore = ($score / $validated['total_items']) * 100;
            \App\Models\Quiz_exam_activity::updateOrCreate(
                [
                    'user_id' => $teacherId,
                    'full_name' => $fullName,
                    'activity_title' => $validated['exam_name'],
                    'date_taken' => $validated['date_taken'],
                    'activity_type' => 'Exam',
                ],
                [
                    'subject' => $student->subject,
                    'section' => $student->section,
                    'score' => $weightedScore,
                    'total_score' => $validated['total_items'],
                    'weighted_score' => $weightedScore,
                ]
            );
        }

        $redirectParams = [
            'exam_name' => $validated['exam_name'],
            'date_taken' => $validated['date_taken'],
            'total_items' => $validated['total_items'],
        ];
        if ($request->has('selected_section') && $request->selected_section) {
            $redirectParams['section'] = $request->selected_section;
        }
        return redirect()->route('exam.add-scores', $redirectParams)->with('success', 'Scores saved successfully!');
    }

    // ==================== ACTIVITY METHODS ====================
    
    public function createActivityPage()
    {
        $teacherId = Auth::id();
        $recentActivities = \App\Models\Quiz_exam_activity::where('user_id', $teacherId)
            ->where('activity_type', 'Activity')
            ->select('activity_title', 'date_taken', DB::raw('MAX(total_score) as total_score'))
            ->groupBy('activity_title', 'date_taken')
            ->orderBy('date_taken', 'desc')
            ->take(10)
            ->get();
        return view('Activity.CreateActivity', compact('recentActivities'));
    }

    public function createActivity(Request $request)
    {
        $validated = $request->validate([
            'activity_name' => 'required|string|max:255',
            'date_taken' => 'required|date',
            'total_items' => 'required|integer|min:1',
        ]);
        return redirect()->route('activity.add-scores', [
            'activity_name' => $validated['activity_name'],
            'date_taken' => $validated['date_taken'],
            'total_items' => $validated['total_items'],
        ]);
    }

    public function addActivityScores(Request $request)
    {
        $activityName = $request->activity_name;
        $dateTaken = $request->date_taken;
        $totalItems = $request->total_items;
        $selectedSection = $request->section;
        $teacherId = Auth::id();

        $sections = Student::where('teacher_id', $teacherId)->distinct()->orderBy('section')->pluck('section');
        $studentsQuery = Student::where('teacher_id', $teacherId);
        if ($selectedSection) {
            $studentsQuery->where('section', $selectedSection);
        }
        $students = $studentsQuery->orderBy('section')->orderBy('full_name')->get();

        $existingScoresData = \App\Models\Quiz_exam_activity::where('user_id', $teacherId)
            ->where('activity_title', $activityName)
            ->where('date_taken', $dateTaken)
            ->where('activity_type', 'Activity')
            ->get();

        // Convert percentage back to raw score for display
        $existingScores = [];
        foreach ($existingScoresData as $record) {
            $rawScore = round(($record->score / 100) * $totalItems);
            $existingScores[$record->full_name] = $rawScore;
        }

        return view('Activity.AddActivityScores', compact('students', 'activityName', 'dateTaken', 'totalItems', 'existingScores', 'sections', 'selectedSection'));
    }

    public function saveActivityScores(Request $request)
    {
        $validated = $request->validate([
            'activity_name' => 'required|string',
            'date_taken' => 'required|date',
            'total_items' => 'required|integer',
            'scores' => 'required|array',
            'scores.*' => 'nullable|integer|min:0',
        ]);

        $teacherId = Auth::id();
        foreach ($validated['scores'] as $fullName => $score) {
            if ($score === null || $score === '') {
                continue;
            }
            $student = Student::where('teacher_id', $teacherId)->where('full_name', $fullName)->first();
            if (!$student) {
                continue;
            }
            $weightedScore = ($score / $validated['total_items']) * 100;
            \App\Models\Quiz_exam_activity::updateOrCreate(
                [
                    'user_id' => $teacherId,
                    'full_name' => $fullName,
                    'activity_title' => $validated['activity_name'],
                    'date_taken' => $validated['date_taken'],
                    'activity_type' => 'Activity',
                ],
                [
                    'subject' => $student->subject,
                    'section' => $student->section,
                    'score' => $weightedScore,
                    'total_score' => $validated['total_items'],
                    'weighted_score' => $weightedScore,
                ]
            );
        }

        $redirectParams = [
            'activity_name' => $validated['activity_name'],
            'date_taken' => $validated['date_taken'],
            'total_items' => $validated['total_items'],
        ];
        if ($request->has('selected_section') && $request->selected_section) {
            $redirectParams['section'] = $request->selected_section;
        }
        return redirect()->route('activity.add-scores', $redirectParams)->with('success', 'Scores saved successfully!');
    }

    // ==================== PROJECT METHODS ====================
    
    public function createProjectPage()
    {
        $teacherId = Auth::id();
        $recentProjects = \App\Models\Quiz_exam_activity::where('user_id', $teacherId)
            ->where('activity_type', 'Project')
            ->select('activity_title', 'date_taken', DB::raw('MAX(total_score) as total_score'))
            ->groupBy('activity_title', 'date_taken')
            ->orderBy('date_taken', 'desc')
            ->take(10)
            ->get();
        return view('Project.CreateProject', compact('recentProjects'));
    }

    public function createProject(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'date_taken' => 'required|date',
            'total_items' => 'required|integer|min:1',
        ]);
        return redirect()->route('project.add-scores', [
            'project_name' => $validated['project_name'],
            'date_taken' => $validated['date_taken'],
            'total_items' => $validated['total_items'],
        ]);
    }

    public function addProjectScores(Request $request)
    {
        $projectName = $request->project_name;
        $dateTaken = $request->date_taken;
        $totalItems = $request->total_items;
        $selectedSection = $request->section;
        $teacherId = Auth::id();

        $sections = Student::where('teacher_id', $teacherId)->distinct()->orderBy('section')->pluck('section');
        $studentsQuery = Student::where('teacher_id', $teacherId);
        if ($selectedSection) {
            $studentsQuery->where('section', $selectedSection);
        }
        $students = $studentsQuery->orderBy('section')->orderBy('full_name')->get();

        $existingScoresData = \App\Models\Quiz_exam_activity::where('user_id', $teacherId)
            ->where('activity_title', $projectName)
            ->where('date_taken', $dateTaken)
            ->where('activity_type', 'Project')
            ->get();

        // Convert percentage back to raw score for display
        $existingScores = [];
        foreach ($existingScoresData as $record) {
            $rawScore = round(($record->score / 100) * $totalItems);
            $existingScores[$record->full_name] = $rawScore;
        }

        return view('Project.AddProjectScores', compact('students', 'projectName', 'dateTaken', 'totalItems', 'existingScores', 'sections', 'selectedSection'));
    }

    public function saveProjectScores(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'required|string',
            'date_taken' => 'required|date',
            'total_items' => 'required|integer',
            'scores' => 'required|array',
            'scores.*' => 'nullable|integer|min:0',
        ]);

        $teacherId = Auth::id();
        foreach ($validated['scores'] as $fullName => $score) {
            if ($score === null || $score === '') {
                continue;
            }
            $student = Student::where('teacher_id', $teacherId)->where('full_name', $fullName)->first();
            if (!$student) {
                continue;
            }
            $weightedScore = ($score / $validated['total_items']) * 100;
            \App\Models\Quiz_exam_activity::updateOrCreate(
                [
                    'user_id' => $teacherId,
                    'full_name' => $fullName,
                    'activity_title' => $validated['project_name'],
                    'date_taken' => $validated['date_taken'],
                    'activity_type' => 'Project',
                ],
                [
                    'subject' => $student->subject,
                    'section' => $student->section,
                    'score' => $weightedScore,
                    'total_score' => $validated['total_items'],
                    'weighted_score' => $weightedScore,
                ]
            );
        }

        $redirectParams = [
            'project_name' => $validated['project_name'],
            'date_taken' => $validated['date_taken'],
            'total_items' => $validated['total_items'],
        ];
        if ($request->has('selected_section') && $request->selected_section) {
            $redirectParams['section'] = $request->selected_section;
        }
        return redirect()->route('project.add-scores', $redirectParams)->with('success', 'Scores saved successfully!');
    }

    // ==================== RECITATION METHODS ====================
    
    public function createRecitationPage()
    {
        $teacherId = Auth::id();
        $recentRecitations = \App\Models\Quiz_exam_activity::where('user_id', $teacherId)
            ->where('activity_type', 'Recitation')
            ->select('activity_title', 'date_taken', DB::raw('MAX(total_score) as total_score'))
            ->groupBy('activity_title', 'date_taken')
            ->orderBy('date_taken', 'desc')
            ->take(10)
            ->get();
        return view('Recitation.CreateRecitation', compact('recentRecitations'));
    }

    public function createRecitation(Request $request)
    {
        $validated = $request->validate([
            'recitation_name' => 'required|string|max:255',
            'date_taken' => 'required|date',
            'total_items' => 'required|integer|min:1',
        ]);
        return redirect()->route('recitation.add-scores', [
            'recitation_name' => $validated['recitation_name'],
            'date_taken' => $validated['date_taken'],
            'total_items' => $validated['total_items'],
        ]);
    }

    public function addRecitationScores(Request $request)
    {
        $recitationName = $request->recitation_name;
        $dateTaken = $request->date_taken;
        $totalItems = $request->total_items;
        $selectedSection = $request->section;
        $teacherId = Auth::id();

        $sections = Student::where('teacher_id', $teacherId)->distinct()->orderBy('section')->pluck('section');
        $studentsQuery = Student::where('teacher_id', $teacherId);
        if ($selectedSection) {
            $studentsQuery->where('section', $selectedSection);
        }
        $students = $studentsQuery->orderBy('section')->orderBy('full_name')->get();

        $existingScoresData = \App\Models\Quiz_exam_activity::where('user_id', $teacherId)
            ->where('activity_title', $recitationName)
            ->where('date_taken', $dateTaken)
            ->where('activity_type', 'Recitation')
            ->get();

        // Convert percentage back to raw score for display
        $existingScores = [];
        foreach ($existingScoresData as $record) {
            $rawScore = round(($record->score / 100) * $totalItems);
            $existingScores[$record->full_name] = $rawScore;
        }

        return view('Recitation.AddRecitationScores', compact('students', 'recitationName', 'dateTaken', 'totalItems', 'existingScores', 'sections', 'selectedSection'));
    }

    public function saveRecitationScores(Request $request)
    {
        $validated = $request->validate([
            'recitation_name' => 'required|string',
            'date_taken' => 'required|date',
            'total_items' => 'required|integer',
            'scores' => 'required|array',
            'scores.*' => 'nullable|integer|min:0',
        ]);

        $teacherId = Auth::id();
        foreach ($validated['scores'] as $fullName => $score) {
            if ($score === null || $score === '') {
                continue;
            }
            $student = Student::where('teacher_id', $teacherId)->where('full_name', $fullName)->first();
            if (!$student) {
                continue;
            }
            $weightedScore = ($score / $validated['total_items']) * 100;
            \App\Models\Quiz_exam_activity::updateOrCreate(
                [
                    'user_id' => $teacherId,
                    'full_name' => $fullName,
                    'activity_title' => $validated['recitation_name'],
                    'date_taken' => $validated['date_taken'],
                    'activity_type' => 'Recitation',
                ],
                [
                    'subject' => $student->subject,
                    'section' => $student->section,
                    'score' => $weightedScore,
                    'total_score' => $validated['total_items'],
                    'weighted_score' => $weightedScore,
                ]
            );
        }

        $redirectParams = [
            'recitation_name' => $validated['recitation_name'],
            'date_taken' => $validated['date_taken'],
            'total_items' => $validated['total_items'],
        ];
        if ($request->has('selected_section') && $request->selected_section) {
            $redirectParams['section'] = $request->selected_section;
        }
        return redirect()->route('recitation.add-scores', $redirectParams)->with('success', 'Scores saved successfully!');
    }
}
