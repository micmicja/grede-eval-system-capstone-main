<x-layouts.app :title="'Student Report - ' . $student->full_name">
    <div class="container-fluid py-4">
        
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="fw-bold">Student Performance Report</h2>
                <p class="text-muted mb-0">
                    <strong>Name:</strong> {{ $student->full_name }} | 
                    <strong>Section:</strong> {{ $student->section }} | 
                    <strong>Subject:</strong> {{ $student->subject }}
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group me-2" role="group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="material-symbols-rounded" style="font-size: 18px; vertical-align: middle;">download</span>
                        Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('export.student-report', ['student' => $student->id, 'format' => 'pdf']) }}"><i class="fas fa-file-pdf"></i> Export as PDF</a></li>
                        <li><a class="dropdown-item" href="{{ route('export.student-report', ['student' => $student->id, 'format' => 'excel']) }}"><i class="fas fa-file-excel"></i> Export as Excel</a></li>
                        <li><a class="dropdown-item" href="{{ route('export.student-report', ['student' => $student->id, 'format' => 'word']) }}"><i class="fas fa-file-word"></i> Export as Word</a></li>
                    </ul>
                </div>
                <a href="{{ route('Dashboard.teacher') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <p class="text-muted small mb-0">Showing period: <strong>{{ \Carbon\Carbon::parse($start)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($end)->format('M d, Y') }}</strong></p>
            </div>
        </div>

        <!-- Semester / Year selector -->
        <div class="row mb-3">
            <div class="col-md-12">
                <form method="GET" action="{{ route('student.report', $student->id) }}" class="d-flex gap-2 justify-content-end align-items-center">
                    <label class="mb-0 me-2 text-muted">Period:</label>
                    <select name="semester" id="semester" class="form-select form-select-sm" style="width:180px;">
                        <option value="0" {{ (isset($semester) && $semester==0) ? 'selected' : '' }}>Full Year</option>
                        <option value="1" {{ (isset($semester) && $semester==1) ? 'selected' : '' }}>Semester 1 (Jan–Jun)</option>
                        <option value="2" {{ (isset($semester) && $semester==2) ? 'selected' : '' }}>Semester 2 (Jul–Dec)</option>
                    </select>

                    <select name="year" id="year" class="form-select form-select-sm" style="width:120px;">
                        @php $cy = \Carbon\Carbon::now()->year; @endphp
                        @for($y = $cy - 1; $y <= $cy + 1; $y++)
                            <option value="{{ $y }}" {{ (isset($year) && $year == $y) ? 'selected' : ($y == $cy && !isset($year) ? 'selected' : '') }}>{{ $y }}</option>
                        @endfor
                    </select>

                    <button type="submit" class="btn btn-sm btn-outline-primary">Apply</button>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <!-- Attendance Summary -->
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Attendance</p>
                                <h4 class="fw-bold mb-0">{{ number_format($attendancePercentage, 1) }}%</h4>
                                <small class="text-muted">{{ $presentDays }}/{{ $totalAttendanceDays }} Days</small>
                            </div>
                            <div style="font-size: 32px; color: #28a745;">
                                <span class="material-symbols-rounded">check_circle</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quiz Summary -->
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Quiz Average</p>
                                <h4 class="fw-bold mb-0">{{ number_format($averageQuizScore, 1) }}/100</h4>
                                <small class="text-muted">{{ $totalQuizzes }} Quizzes</small>
                            </div>
                            <div style="font-size: 32px; color: #ffc107;">
                                <span class="material-symbols-rounded">edit_note</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exam Summary -->
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Exam Average</p>
                                <h4 class="fw-bold mb-0">{{ number_format($averageExamScore, 1) }}/100</h4>
                                <small class="text-muted">{{ $totalExams }} Exams</small>
                            </div>
                            <div style="font-size: 32px; color: #dc3545;">
                                <span class="material-symbols-rounded">assignment</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overall Grade -->
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Overall Score</p>
                                @php
                                    // Use weighted overall computed by controller (overallWeighted)
                                    $overallScore = isset($overallWeighted) ? $overallWeighted : 0;
                                    $grade = '';
                                    if ($overallScore >= 90) $grade = 'A';
                                    elseif ($overallScore >= 80) $grade = 'B';
                                    elseif ($overallScore >= 70) $grade = 'C';
                                    elseif ($overallScore >= 60) $grade = 'D';
                                    else $grade = 'F';
                                @endphp
                                <h4 class="fw-bold mb-0">{{ $grade }}</h4>
                                <small class="text-muted">{{ number_format($overallScore, 1) }}/100</small>
                                @if(isset($settings))
                                    <div class="mt-2 small text-muted">Weights: Q{{ $settings->quiz_weight ?? 25 }}% | Ex{{ $settings->exam_weight ?? 25 }}% | Ac{{ $settings->activity_weight ?? 25 }}% | Pr{{ $settings->project_weight ?? 15 }}% | Re{{ $settings->recitation_weight ?? 10 }}% | Re{{ $settings->attendance_weight ?? 10 }}%</div>
                                @endif
                            </div>
                            <div style="font-size: 32px; color: #0d6efd;">
                                <span class="material-symbols-rounded">grade</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Summary -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Activities Average</p>
                                <h4 class="fw-bold mb-0">{{ number_format($averageActivityScore ?? 0, 1) }}/100</h4>
                                <small class="text-muted">{{ $totalActivities ?? 0 }} Activities</small>
                            </div>
                            <div style="font-size: 32px; color: #6f42c1;">
                                <span class="material-symbols-rounded">local_activity</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project & Recitation Summary -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Project Average</p>
                                <h4 class="fw-bold mb-0">{{ number_format($averageProjectScore ?? 0, 1) }}/100</h4>
                                <small class="text-muted">{{ $totalProjects ?? 0 }} Projects</small>
                            </div>
                            <div style="font-size: 32px; color: #17a2b8;">
                                <span class="material-symbols-rounded">school</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Recitation Average</p>
                                <h4 class="fw-bold mb-0">{{ number_format($averageRecitationScore ?? 0, 1) }}/100</h4>
                                <small class="text-muted">{{ $totalRecitations ?? 0 }} Recitations</small>
                            </div>
                            <div style="font-size: 32px; color: #0dcaf0;">
                                <span class="material-symbols-rounded">record_voice_over</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs for Details -->
        <ul class="nav nav-tabs mb-4" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab">
                    <span class="material-symbols-rounded" style="font-size: 20px;">checklist</span> Attendance
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="quiz-tab" data-bs-toggle="tab" data-bs-target="#quiz" type="button" role="tab">
                    <span class="material-symbols-rounded" style="font-size: 20px;">edit_note</span> Quizzes
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="exam-tab" data-bs-toggle="tab" data-bs-target="#exam" type="button" role="tab">
                    <span class="material-symbols-rounded" style="font-size: 20px;">assignment</span> Exams
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">
                    <span class="material-symbols-rounded" style="font-size: 20px;">local_activity</span> Activities
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="project-tab" data-bs-toggle="tab" data-bs-target="#project" type="button" role="tab">
                    <span class="material-symbols-rounded" style="font-size: 20px;">school</span> Projects
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="recitation-tab" data-bs-toggle="tab" data-bs-target="#recitation" type="button" role="tab">
                    <span class="material-symbols-rounded" style="font-size: 20px;">record_voice_over</span> Recitations
                </button>
            </li>
        </ul>

        <!-- Tab Contents -->
        <div class="tab-content" id="reportTabsContent">
            <!-- Attendance Tab -->
            <div class="tab-pane fade show active" id="attendance" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Attendance Records</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendanceRecords as $record)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($record->date)->format('M d, Y (D)') }}</td>
                                        <td>
                                            @if($record->present)
                                                <span class="badge bg-success">Present</span>
                                            @else
                                                <span class="badge bg-danger">Absent</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">
                                            No attendance records found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quiz Tab -->
            <div class="tab-pane fade" id="quiz" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Quiz Records</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Quiz Title</th>
                                    <th>Date Taken</th>
                                    <th>Score</th>
                                    <th>Weighted Score</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quizRecords as $quiz)
                                    <tr>
                                        <td>{{ $quiz->activity_title }}</td>
                                        <td>{{ \Carbon\Carbon::parse($quiz->date_taken)->format('M d, Y') }}</td>
                                        <td>
                                            <strong>{{ $quiz->score }}/{{ $quiz->total_score ?? 100 }}</strong>
                                        </td>
                                        <td>
                                            {{ number_format($quiz->weighted_score ?? 0, 1) }}%
                                        </td>
                                        <td>
                                            @php $ws = $quiz->weighted_score ?? 0; @endphp
                                            @if($ws >= 80)
                                                <span class="badge bg-success">Excellent</span>
                                            @elseif($ws >= 70)
                                                <span class="badge bg-info">Good</span>
                                            @elseif($ws >= 60)
                                                <span class="badge bg-warning">Fair</span>
                                            @else
                                                <span class="badge bg-danger">Needs Improvement</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No quiz records found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Exam Tab -->
            <div class="tab-pane fade" id="exam" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Exam Records</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Exam Title</th>
                                    <th>Date Taken</th>
                                    <th>Score</th>
                                    <th>Weighted Score</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($examRecords as $exam)
                                    <tr>
                                        <td>{{ $exam->activity_title }}</td>
                                        <td>{{ \Carbon\Carbon::parse($exam->date_taken)->format('M d, Y') }}</td>
                                        <td>
                                            <strong>{{ $exam->score }}/{{ $exam->total_score ?? 100 }}</strong>
                                        </td>
                                        <td>
                                            {{ number_format($exam->weighted_score ?? 0, 1) }}%
                                        </td>
                                        <td>
                                            @php $ws = $exam->weighted_score ?? 0; @endphp
                                            @if($ws >= 80)
                                                <span class="badge bg-success">Excellent</span>
                                            @elseif($ws >= 70)
                                                <span class="badge bg-info">Good</span>
                                            @elseif($ws >= 60)
                                                <span class="badge bg-warning">Fair</span>
                                            @else
                                                <span class="badge bg-danger">Needs Improvement</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No exam records found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Activity Tab -->
            <div class="tab-pane fade" id="activity" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Activity Records</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Activity Title</th>
                                    <th>Date Taken</th>
                                    <th>Score</th>
                                    <th>Weighted Score</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activityRecords as $act)
                                    <tr>
                                        <td>{{ $act->activity_title }}</td>
                                        <td>{{ \Carbon\Carbon::parse($act->date_taken)->format('M d, Y') }}</td>
                                        <td>
                                            <strong>{{ $act->score }}/{{ $act->total_score ?? 100 }}</strong>
                                        </td>
                                        <td>
                                            {{ number_format($act->weighted_score ?? 0, 1) }}%
                                        </td>
                                        <td>
                                            @php $ws = $act->weighted_score ?? 0; @endphp
                                            @if($ws >= 80)
                                                <span class="badge bg-success">Excellent</span>
                                            @elseif($ws >= 70)
                                                <span class="badge bg-info">Good</span>
                                            @elseif($ws >= 60)
                                                <span class="badge bg-warning">Fair</span>
                                            @else
                                                <span class="badge bg-danger">Needs Improvement</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No activity records found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Project Tab -->
            <div class="tab-pane fade" id="project" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Project Records</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Project Title</th>
                                    <th>Date Submitted</th>
                                    <th>Score</th>
                                    <th>Weighted Score</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projectRecords as $record)
                                    <tr>
                                        <td>{{ $record->activity_title }}</td>
                                        <td>{{ \Carbon\Carbon::parse($record->date_taken)->format('M d, Y') }}</td>
                                        <td>{{ $record->score }}/{{ $record->total_score ?? 100 }}</td>
                                        <td>{{ number_format($record->weighted_score ?? 0, 1) }}%</td>
                                        <td>
                                            @php $ws = $record->weighted_score ?? 0; @endphp
                                            @if($ws >= 80)
                                                <span class="badge bg-success">Excellent</span>
                                            @elseif($ws >= 70)
                                                <span class="badge bg-info">Good</span>
                                            @elseif($ws >= 60)
                                                <span class="badge bg-warning">Fair</span>
                                            @else
                                                <span class="badge bg-danger">Needs Improvement</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No project records found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recitation Tab -->
            <div class="tab-pane fade" id="recitation" role="tabpanel">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Recitation Records</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Topic</th>
                                    <th>Date Conducted</th>
                                    <th>Score</th>
                                    <th>Weighted Score</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recitationRecords as $record)
                                    <tr>
                                        <td>{{ $record->activity_title }}</td>
                                        <td>{{ \Carbon\Carbon::parse($record->date_taken)->format('M d, Y') }}</td>
                                        <td>{{ $record->score }}/{{ $record->total_score ?? 100 }}</td>
                                        <td>{{ number_format($record->weighted_score ?? 0, 1) }}%</td>
                                        <td>
                                            @php $ws = $record->weighted_score ?? 0; @endphp
                                            @if($ws >= 80)
                                                <span class="badge bg-success">Excellent</span>
                                            @elseif($ws >= 70)
                                                <span class="badge bg-info">Good</span>
                                            @elseif($ws >= 60)
                                                <span class="badge bg-warning">Fair</span>
                                            @else
                                                <span class="badge bg-danger">Needs Improvement</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No recitation records found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            border: none;
            border-bottom: 2px solid transparent;
            color: #6c757d;
            font-weight: 500;
            transition: all 0.3s;
        }

        .nav-link:hover {
            color: #0d6efd;
            border-bottom-color: #e9ecef;
        }

        .nav-link.active {
            color: #0d6efd;
            border-bottom-color: #0d6efd;
            background: transparent;
        }

        .material-symbols-rounded {
            font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 40;
            vertical-align: middle;
        }
    </style>
</x-layouts.app>
