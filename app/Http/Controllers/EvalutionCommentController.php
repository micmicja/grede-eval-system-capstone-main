<?php

namespace App\Http\Controllers;

use App\Models\EvalutionComment;
use App\Models\StudentObservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        $riskObservations = StudentObservation::with(['student', 'teacher'])
            ->where('referred_to_councilor', true)
            ->whereIn('risk_status', ['High Risk', 'Mid High Risk'])
            ->latest()
            ->get();

        // Kunin ang mga Users na ang role ay 'teacher' (o 'instructor') 
        // at bilangin ang flagged students gamit ang relationship 'flagCreated'
        $instructors = User::where('role', 'teacher') // Siguraduhin na 'teacher' ang role name mo
            ->withCount('flagCreated')
            ->get();

        $stats = [
            // high_priority should use urgency, not workflow status
            'high_priority' => EvalutionComment::where('urgency', 'high')->count(),
            'ongoing' => EvalutionComment::where('status', 'ongoing')->count(),
            'resolved' => EvalutionComment::where('status', 'resolved')
                ->whereMonth('updated_at', now()->month)->count(),
            'at_risk_students' => StudentObservation::where('referred_to_councilor', true)
                ->whereIn('risk_status', ['High Risk', 'Mid High Risk'])
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
}