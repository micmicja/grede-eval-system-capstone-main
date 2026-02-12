<?php

namespace App\Http\Controllers;

use App\Models\EvalutionComment;
use App\Models\StudentObservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class EvalutionCommentController extends Controller
{
    //

    public function index(Request $request)
    {
        // Build base query with eager loads
        $query = EvalutionComment::with(['student', 'teacher']);

        // Apply search term to comment text or student/teacher name
        if ($request->filled('q')) {
            $q = $request->query('q');
            $query->where(function ($qbuilder) use ($q) {
                $qbuilder->where('comments', 'like', "%{$q}%")
                    ->orWhereHas('student', function ($s) use ($q) {
                        $s->where('full_name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('teacher', function ($t) use ($q) {
                        $t->where('full_name', 'like', "%{$q}%");
                    });
            });
        }

        // Filter by teacher who referred
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->query('teacher_id'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        // Filter by urgency (low|mid|high)
        if ($request->filled('urgency')) {
            $query->where('urgency', $request->query('urgency'));
        }

        // Filter by category (if applicable)
        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        // Date range filters
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->query('to'));
        }

        // Latest first and paginate
        $evaluations = $query->latest()->paginate(15)->withQueryString();

        // Get risk-based observations (High Risk and Mid High Risk students)
        $riskObservationsQuery = StudentObservation::with(['student', 'teacher'])
            ->where('referred_to_councilor', true)
            ->whereIn('risk_status', ['High Risk', 'Mid High Risk']);

        // Apply filters to risk observations
        if ($request->filled('q')) {
            $q = $request->query('q');
            $riskObservationsQuery->where(function ($qbuilder) use ($q) {
                $qbuilder->whereHas('student', function ($s) use ($q) {
                    $s->where('full_name', 'like', "%{$q}%");
                })
                ->orWhereHas('teacher', function ($t) use ($q) {
                    $t->where('full_name', 'like', "%{$q}%");
                });
            });
        }

        if ($request->filled('teacher_id')) {
            $riskObservationsQuery->where('teacher_id', $request->query('teacher_id'));
        }

        if ($request->filled('status')) {
            $riskObservationsQuery->where('counseling_status', $request->query('status'));
        }

        $riskObservations = $riskObservationsQuery->latest()->get();

        // Get teachers with count of their active observations (exclude resolved)
        $instructors = User::where('role', 'teacher')
            ->withCount(['observations' => function ($query) {
                $query->where('referred_to_councilor', true)
                      ->where('counseling_status', '!=', 'resolved');
            }])
            ->get()
            ->map(function ($teacher) {
                $teacher->flag_created_count = $teacher->observations_count;
                return $teacher;
            });

        $stats = [
            // High priority based on High Risk observations (exclude resolved)
            'high_priority' => StudentObservation::where('referred_to_councilor', true)
                ->where('risk_status', 'High Risk')
                ->where('counseling_status', '!=', 'resolved')
                ->count(),
            'ongoing' => StudentObservation::where('counseling_status', 'ongoing')->count(),
            'resolved' => StudentObservation::where('counseling_status', 'resolved')
                ->whereMonth('updated_at', now()->month)->count(),
            'at_risk_students' => StudentObservation::where('referred_to_councilor', true)
                ->whereIn('risk_status', ['High Risk', 'Mid High Risk'])
                ->where('counseling_status', '!=', 'resolved')
                ->count(),
        ];

        return view('Councilor.Dashboard', compact('evaluations', 'stats', 'instructors', 'riskObservations'));
    }

    /**
     * JSON search endpoint for live search or API usage
     */
    public function search(Request $request)
    {
        $query = EvalutionComment::with(['student', 'teacher']);

        if ($request->filled('q')) {
            $q = $request->query('q');
            $query->where(function ($qbuilder) use ($q) {
                $qbuilder->where('comments', 'like', "%{$q}%")
                    ->orWhereHas('student', function ($s) use ($q) {
                        $s->where('full_name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('teacher', function ($t) use ($q) {
                        $t->where('full_name', 'like', "%{$q}%");
                    });
            });
        }

        $results = $query->latest()->limit(25)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'student' => $item->student?->full_name,
                'teacher' => $item->teacher?->full_name,
                'status' => $item->status,
                'urgency' => $item->urgency,
                'category' => $item->category,
                'comments' => $item->comments,
                'scheduled_at' => optional($item->scheduled_at)->toDateTimeString(),
                'created_at' => $item->created_at->toDateTimeString(),
            ];
        });

        return response()->json(['data' => $results]);
    }

    public function setSchedule(Request $request, $id)
    {
        // Accept broad date input, parse it and validate afterward to avoid client timezone issues
        $request->validate([
            'scheduled_at' => 'required|date',
        ]);

        $evaluation = EvalutionComment::findOrFail($id);

        // Parse scheduled date/time to a consistent Carbon instance
        try {
            $scheduledAt = Carbon::parse($request->scheduled_at);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['scheduled_at' => 'Invalid date/time format.'])->withInput();
        }

        // Ensure scheduled time is in the future (allow small leeway, must be > now)
        if (! $scheduledAt->isFuture()) {
            return redirect()->back()->withErrors(['scheduled_at' => 'Please choose a future date and time.'])->withInput();
        }

        // Check for conflicts within the same minute (minute-resolution matching avoids seconds mismatches)
        $start = $scheduledAt->copy()->startOfMinute();
        $end = $scheduledAt->copy()->endOfMinute();

        $conflict = EvalutionComment::whereBetween('scheduled_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->where('status', '!=', 'resolved')
            ->where('id', '!=', $evaluation->id)
            ->exists();

        if ($conflict) {
            return redirect()->back()->withErrors(['scheduled_at' => 'This schedule cannot be booked because the selected date and time are already taken. Please choose a different date or time.'])->withInput();
        }

        // Siguraduhin na ang 'scheduled_at' ay nasa $fillable ng EvalutionComment Model
        $evaluation->update([
            'scheduled_at' => $scheduledAt,
            'status' => 'ongoing'
        ]);

        return redirect()->back()->with('success', 'Schedule has been set successfully!');
    }


    // Magdagdag ng route method sa controller
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,ongoing,resolved',
        ]);

        $evaluation = EvalutionComment::findOrFail($id);
        $evaluation->update([
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Status updated successfully!');
    }

    /**
     * Schedule a counseling session for a risk observation
     */
    public function scheduleRiskObservation(Request $request, $id)
    {
        $request->validate([
            'scheduled_at' => 'required|date',
        ]);

        $observation = StudentObservation::findOrFail($id);

        try {
            $scheduledAt = Carbon::parse($request->scheduled_at);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['scheduled_at' => 'Invalid date/time format.'])->withInput();
        }

        if (!$scheduledAt->isFuture()) {
            return redirect()->back()->withErrors(['scheduled_at' => 'Please choose a future date and time.'])->withInput();
        }

        // Check for conflicts with existing evaluations
        $start = $scheduledAt->copy()->startOfMinute();
        $end = $scheduledAt->copy()->endOfMinute();

        $conflict = EvalutionComment::whereBetween('scheduled_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->where('status', '!=', 'resolved')
            ->exists();

        if ($conflict) {
            return redirect()->back()->withErrors(['scheduled_at' => 'This schedule cannot be booked because the selected date and time are already taken. Please choose a different date or time.'])->withInput();
        }

        // Update observation with scheduled date
        $observation->update([
            'scheduled_at' => $scheduledAt,
            'counseling_status' => 'scheduled'
        ]);

        return redirect()->back()->with('success', 'Counseling schedule has been set successfully for ' . $observation->student->full_name . '!');
    }

    /**
     * Update counseling status for a student observation
     */
    public function updateObservationStatus(Request $request, $id)
    {
        $request->validate([
            'counseling_status' => 'required|in:pending,ongoing,resolved',
        ]);

        $observation = StudentObservation::findOrFail($id);
        $observation->update([
            'counseling_status' => $request->counseling_status
        ]);

        return redirect()->back()->with('success', 'Counseling status updated successfully!');
    }

    /**
     * Export counseling referrals to PDF, Excel, or Word
     */
    public function exportReferrals(Request $request)
    {
        $counselor = Auth::user();
        
        // Build query for evaluations
        $query = EvalutionComment::with(['student', 'teacher']);
        
        if ($request->filled('q')) {
            $q = $request->query('q');
            $query->where(function ($qbuilder) use ($q) {
                $qbuilder->where('comments', 'like', "%{$q}%")
                    ->orWhereHas('student', function ($s) use ($q) {
                        $s->where('full_name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('teacher', function ($t) use ($q) {
                        $t->where('full_name', 'like', "%{$q}%");
                    });
            });
        }
        
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->query('teacher_id'));
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        
        $evaluations = $query->latest()->get();
        
        // Build query for risk observations
        $riskObservationsQuery = StudentObservation::with(['student', 'teacher'])
            ->where('referred_to_councilor', true);
        
        if ($request->filled('q')) {
            $q = $request->query('q');
            $riskObservationsQuery->where(function ($qbuilder) use ($q) {
                $qbuilder->whereHas('student', function ($s) use ($q) {
                    $s->where('full_name', 'like', "%{$q}%");
                })
                ->orWhereHas('teacher', function ($t) use ($q) {
                    $t->where('full_name', 'like', "%{$q}%");
                });
            });
        }
        
        if ($request->filled('teacher_id')) {
            $riskObservationsQuery->where('teacher_id', $request->query('teacher_id'));
        }
        
        if ($request->filled('status')) {
            $riskObservationsQuery->where('counseling_status', $request->query('status'));
        }
        
        $riskObservations = $riskObservationsQuery->latest()->get();
        
        $format = $request->input('format', 'pdf');
        $filename = 'counseling-referrals-' . Carbon::now()->format('Y-m-d');
        
        if ($format === 'excel') {
            return $this->generateReferralsCSV($evaluations, $riskObservations, $filename);
        }
        
        if ($format === 'word') {
            return $this->generateReferralsWord($evaluations, $riskObservations, $counselor, $filename);
        }
        
        // Default to PDF
        $pdf = Pdf::loadView('exports.counseling-referrals', [
            'evaluations' => $evaluations,
            'riskObservations' => $riskObservations,
            'counselor' => $counselor,
            'date' => Carbon::now()->format('F d, Y'),
            'filters' => [
                'search' => $request->input('q'),
                'teacher' => $request->input('teacher_id'),
                'status' => $request->input('status')
            ]
        ]);
        
        return $pdf->download($filename . '.pdf');
    }

    /**
     * Generate CSV for counseling referrals
     */
    private function generateReferralsCSV($evaluations, $riskObservations, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];
        
        $callback = function() use ($evaluations, $riskObservations) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['COUNSELING REFERRALS']);
            fputcsv($file, ['Generated on: ' . Carbon::now()->format('F d, Y')]);
            fputcsv($file, []);
            
            fputcsv($file, ['No.', 'Type', 'Student Name', 'Section', 'Referred By', 'Reason/Risk Level', 'Status', 'Schedule']);
            
            $index = 1;
            
            // Export risk observations
            foreach ($riskObservations as $obs) {
                $behaviors = is_array($obs->observed_behaviors) 
                    ? $obs->observed_behaviors 
                    : json_decode($obs->observed_behaviors, true) ?? [];
                $behaviorText = count($behaviors) > 0 ? implode('; ', $behaviors) : 'Risk Assessment';
                
                fputcsv($file, [
                    $index++,
                    'Risk Assessment',
                    $obs->student->full_name ?? 'N/A',
                    $obs->student->section ?? 'N/A',
                    $obs->teacher->full_name ?? 'N/A',
                    $obs->risk_status . ' - ' . $behaviorText,
                    ucfirst($obs->counseling_status ?? 'pending'),
                    $obs->scheduled_at ? $obs->scheduled_at->format('M d, Y h:i A') : 'Not Scheduled'
                ]);
            }
            
            // Export regular evaluations
            foreach ($evaluations as $eval) {
                fputcsv($file, [
                    $index++,
                    'Referral',
                    $eval->student->full_name ?? 'N/A',
                    $eval->student->section ?? 'N/A',
                    $eval->teacher->full_name ?? 'N/A',
                    $eval->comments ?? 'N/A',
                    ucfirst($eval->status ?? 'pending'),
                    $eval->scheduled_at ? $eval->scheduled_at->format('M d, Y h:i A') : 'Not Set'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate Word document for counseling referrals
     */
    private function generateReferralsWord($evaluations, $riskObservations, $counselor, $filename)
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        
        // Header
        $section->addText(
            'COUNSELING REFERRALS',
            ['bold' => true, 'size' => 18, 'color' => '1a237e'],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        $section->addText(
            'Guidance Counselor: ' . ($counselor->full_name ?? 'N/A'),
            ['size' => 10, 'color' => '555555'],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        $section->addText(
            'Date: ' . now()->format('F d, Y'),
            ['size' => 10, 'color' => '555555'],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        $section->addTextBreak(1);
        
        $section->addText(
            'Total Referrals: ' . ($riskObservations->count() + $evaluations->count()),
            ['bold' => true, 'size' => 11, 'color' => '1a237e']
        );
        $section->addTextBreak(1);
        
        // Create table
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => 'd1d5db',
            'width' => 5000
        ]);
        
        // Header row
        $table->addRow(500);
        $table->addCell(800, ['bgColor' => '1a237e'])->addText('No.', ['bold' => true, 'color' => 'FFFFFF']);
        $table->addCell(1500, ['bgColor' => '1a237e'])->addText('Type', ['bold' => true, 'color' => 'FFFFFF']);
        $table->addCell(2500, ['bgColor' => '1a237e'])->addText('Student', ['bold' => true, 'color' => 'FFFFFF']);
        $table->addCell(2000, ['bgColor' => '1a237e'])->addText('Referred By', ['bold' => true, 'color' => 'FFFFFF']);
        $table->addCell(2500, ['bgColor' => '1a237e'])->addText('Reason/Risk', ['bold' => true, 'color' => 'FFFFFF']);
        $table->addCell(1500, ['bgColor' => '1a237e'])->addText('Status', ['bold' => true, 'color' => 'FFFFFF']);
        
        $index = 1;
        
        // Add risk observations
        foreach ($riskObservations as $obs) {
            $bgColor = ($index % 2 == 0) ? 'FFFFFF' : 'f9fafb';
            $behaviors = is_array($obs->observed_behaviors) 
                ? $obs->observed_behaviors 
                : json_decode($obs->observed_behaviors, true) ?? [];
            $behaviorText = count($behaviors) > 0 ? implode(', ', array_slice($behaviors, 0, 2)) : 'Risk Assessment';
            if(count($behaviors) > 2) $behaviorText .= '...';
            
            $table->addRow();
            $table->addCell(800, ['bgColor' => $bgColor])->addText((string)$index++);
            $table->addCell(1500, ['bgColor' => $bgColor])->addText('Risk Assessment');
            $table->addCell(2500, ['bgColor' => $bgColor])->addText((string)($obs->student->full_name ?? 'N/A'));
            $table->addCell(2000, ['bgColor' => $bgColor])->addText((string)($obs->teacher->full_name ?? 'N/A'));
            $table->addCell(2500, ['bgColor' => $bgColor])->addText($obs->risk_status);
            $table->addCell(1500, ['bgColor' => $bgColor])->addText(ucfirst($obs->counseling_status ?? 'pending'));
        }
        
        // Add regular evaluations
        foreach ($evaluations as $eval) {
            $bgColor = ($index % 2 == 0) ? 'FFFFFF' : 'f9fafb';
            
            $table->addRow();
            $table->addCell(800, ['bgColor' => $bgColor])->addText((string)$index++);
            $table->addCell(1500, ['bgColor' => $bgColor])->addText('Referral');
            $table->addCell(2500, ['bgColor' => $bgColor])->addText((string)($eval->student->full_name ?? 'N/A'));
            $table->addCell(2000, ['bgColor' => $bgColor])->addText((string)($eval->teacher->full_name ?? 'N/A'));
            $table->addCell(2500, ['bgColor' => $bgColor])->addText(substr($eval->comments ?? 'N/A', 0, 50) . '...');
            $table->addCell(1500, ['bgColor' => $bgColor])->addText(ucfirst($eval->status ?? 'pending'));
        }
        
        $section->addTextBreak(2);
        $section->addText(
            'Generated by Grade Evaluation System',
            ['size' => 8, 'color' => '666666'],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        
        $fileName = $filename . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'word_');
        
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);
        
        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
