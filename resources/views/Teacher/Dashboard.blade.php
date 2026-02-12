<x-layouts.app title="Teacher Dashboard">

    {{-- GOOGLE FONTS + GOOGLE ICONS --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet" />

    <style>
        /* Apply fonts globally */
        body {
            background: #f5f7fb;
            font-family: 'Inter', 'Poppins', sans-serif !important;
        }

        .material-symbols-rounded {
            font-variation-settings:
                'FILL' 0,
                'wght' 500,
                'GRAD' 0,
                'opsz' 40;
            vertical-align: middle;
            font-size: 22px;
            margin-right: 8px;
        }

        /* ===== SIDEBAR ===== */
        .sidebar-wrapper {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
        }

        .sidebar-user-box {
            background: #f2f6ff;
            border: 1px solid #e4edff;
            padding: 16px;
            border-radius: 12px;
            font-weight: 600;
            text-align: center;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            border-radius: 8px;
            color: #4a4a4a;
            font-weight: 500;
            transition: 0.2s;
            text-decoration: none;
        }

        .sidebar-menu a:hover {
            background: #e8efff;
            color: #0d6efd;
            transform: translateX(4px);
        }

        /* ===== CARDS ===== */
        .card {
            border-radius: 18px;
            border: none;
            overflow: hidden;
            box-shadow: 0 6px 26px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background: transparent;
            border: none;
            padding: 18px 22px;
        }

        .table thead th {
            background: #f3f6fb;
            border-bottom: 2px solid #edf1f7;
            font-weight: 600;
        }

        .btn-primary {
            border-radius: 12px;
            padding: 8px 20px;
            font-weight: 600;
        }

        .btn-outline-primary {
            border-radius: 10px;
            font-weight: 500;
        }

        /* Hide any cyan observation bars or overlays */
        *[style*="background-color: rgb(0, 188, 212)"],
        *[style*="background-color: #00bcd4"],
        *[style*="background-color: rgb(23, 162, 184)"],
        *[style*="background-color: #17a2b8"],
        *[style*="background: rgb(0, 188, 212)"],
        *[style*="background: #00bcd4"],
        *[style*="background: rgb(23, 162, 184)"],
        *[style*="background: #17a2b8"] {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
        }

        /* Hide elements positioned at bottom */
        *[style*="position: fixed"][style*="bottom: 0"],
        *[style*="position: absolute"][style*="bottom: 0"] {
            display: none !important;
        }

        /* Hide any bottom overlays containing observation */
        div:has-text("Observation") {
            display: none !important;
        }
    </style>

    <script>
        // Hide observation bars immediately
        document.addEventListener('DOMContentLoaded', function() {
            function removeObservationBars() {
                // Remove elements with "Observation" text at the bottom
                const elements = document.querySelectorAll('*');
                elements.forEach(el => {
                    if (el.textContent && el.textContent.trim() === 'Observation') {
                        const style = window.getComputedStyle(el);
                        const parent = el.parentElement;
                        
                        // Check if it's positioned at bottom
                        if (style.position === 'fixed' || style.position === 'absolute') {
                            el.remove();
                        } else if (parent) {
                            const parentStyle = window.getComputedStyle(parent);
                            if (parentStyle.position === 'fixed' || parentStyle.position === 'absolute') {
                                if (parentStyle.bottom === '0px' || parseInt(parentStyle.bottom) < 100) {
                                    parent.remove();
                                }
                            }
                        }
                    }
                    
                    // Remove cyan colored elements at bottom
                    const style = window.getComputedStyle(el);
                    const bgColor = style.backgroundColor;
                    if (bgColor.includes('0, 188, 212') || bgColor.includes('23, 162, 184')) {
                        if (style.position === 'fixed' || style.position === 'absolute') {
                            el.remove();
                        }
                    }
                });
            }
            
            // Run multiple times to catch dynamically added content
            removeObservationBars();
            setTimeout(removeObservationBars, 100);
            setTimeout(removeObservationBars, 500);
            setTimeout(removeObservationBars, 1000);
            setTimeout(removeObservationBars, 2000);
            
            // Watch for new elements being added
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        setTimeout(removeObservationBars, 50);
                    }
                });
            });
            observer.observe(document.body, { childList: true, subtree: true });
        });
    </script>

    <div class="container-fluid p-4">

        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-check-circle"></i> Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-exclamation-triangle"></i> Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Header -->
        <div class="p-3">
            <div class="col-md-6">
                <h3 class="fw-bold mb-0">
                    Good day , {{ Auth::user()->full_name ?? 'Teacher' }}
                </h3>
            </div>

        </div>

        @php
            $cards = [
                [
                    'label' => 'Attendance',
                    'value' => $percentage->attendance_weight ?? 0,
                    'icon' => 'check_circle',
                    'color' => '#28a745',
                ],
                [
                    'label' => 'Quiz',
                    'value' => $percentage->quiz_weight ?? 0,
                    'icon' => 'edit_note',
                    'color' => '#ffc107',
                ],
                [
                    'label' => 'Exam',
                    'value' => $percentage->exam_weight ?? 0,
                    'icon' => 'assignment',
                    'color' => '#dc3545',
                ],
                [
                    'label' => 'Activities',
                    'value' => $percentage->activity_weight ?? 0,
                    'icon' => 'edit_note',
                    'color' => '#dc3545',
                ],
                [
                    'label' => 'Recitation',
                    'value' => $percentage->recitation_weight ?? 0,
                    'icon' => 'check_circle',
                    'color' => '#dc3545',
                ],
                [
                    'label' => 'Projects',
                    'value' => $percentage->project_weight ?? 0,
                    'icon' => 'assignment',
                    'color' => '#dc3545',
                ],
            ];
        @endphp


        <!-- Summary Cards -->
        <div class="row mb-4">

            <!-- Total Students -->
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Total Students</p>
                                <h4 class="fw-bold mb-0">{{ $students->count() }}</h4>
                            </div>
                            <div style="font-size: 32px; color: #0d6efd;">
                                <span class="material-symbols-rounded">groups</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @foreach ($cards as $card)
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">{{ $card['label'] }}</p>
                                <h4 class="fw-bold mb-0">{{ $card['value'] }}%</h4>
                            </div>
                            <div style="font-size: 32px; color: {{ $card['color'] }};">
                                <span class="material-symbols-rounded">{{ $card['icon'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

        </div>


        <div class="row">

            <!-- SIDEBAR -->
            <aside class="col-lg-3 mb-4">
                <div class="sidebar-wrapper">
                    <div class="sidebar-user-box mb-4">
                        {{ Auth::user()->full_name ?? 'Teacher Name' }}
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="mb-4">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100" style="border-radius: 8px;">
                            <span class="material-symbols-rounded" style="font-size: 18px;">logout</span>
                            Logout
                        </button>
                    </form>

                    <div class="sidebar-menu">
                        <a href="{{ route('attendance.show') }}">
                            <span class="material-symbols-rounded">checklist</span>
                            Take Attendance
                        </a>

                        <a href="{{ route('quiz.create-quiz-page') }}">
                            <span class="material-symbols-rounded">edit_note</span>
                            Record Quiz
                        </a>

                        <a href="{{ route('exam.create-exam-page') }}">
                            <span class="material-symbols-rounded">assignment</span>
                            Record Exam
                        </a>

                        <a href="{{ route('activity.create-activity-page') }}">
                            <span class="material-symbols-rounded">menu_book</span>
                            Record Activity
                        </a>

                        <a href="{{ route('project.create-project-page') }}">
                            <span class="material-symbols-rounded">school</span>
                            Projects
                        </a>

                        <a href="{{ route('recitation.create-recitation-page') }}">
                            <span class="material-symbols-rounded">record_voice_over</span>
                            Record Recitation
                        </a>

                        <a href="{{ route('teacher.settings') }}">
                            <span class="material-symbols-rounded">tune</span>
                            Grade Allocation
                        </a>
                    </div>
                </div>
            </aside>


            <!-- MAIN CONTENT -->
            <section class="col-lg-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold">Student List</h5>
                        <div>
                            @if ($percentage === null)
                            <span class="text-danger small">Please set grade allocation in settings.</span>

                            @else
                            <div class="btn-group me-2" role="group">
                                <button type="button" class="btn btn-sm btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="material-symbols-rounded" style="font-size: 18px;">download</span>
                                    Export Student List
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('export.student-list', ['format' => 'pdf']) }}"><i class="fas fa-file-pdf"></i> Export as PDF</a></li>
                                    <li><a class="dropdown-item" href="{{ route('export.student-list', ['format' => 'excel']) }}"><i class="fas fa-file-excel"></i> Export as Excel</a></li>
                                    <li><a class="dropdown-item" href="{{ route('export.student-list', ['format' => 'word']) }}"><i class="fas fa-file-word"></i> Export as Word</a></li>
                                </ul>
                            </div>
                            @if(isset($studentsWithRisk) && $studentsWithRisk->count() > 0)
                            <button type="button" class="btn btn-sm btn-warning me-2" data-bs-toggle="modal" data-bs-target="#riskAssessmentExportModal">
                                <span class="material-symbols-rounded" style="font-size: 18px;">download</span>
                                Export Risk Assessment
                            </button>
                            @endif
                            <a href="{{ route('add-student') }}" class="btn btn-sm btn-outline-primary">
                                <span class="material-symbols-rounded" style="font-size: 18px;">person_add</span>
                                Add Student
                            </a>
                            @endif
                        </div>
                    </div>

                    {{-- success message --}}
                    @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Section</th>
                                        <th>Counseling Schedule</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($students as $student)
                                    <tr>
                                        <td class="fw-bold">{{ $student->full_name }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $student->section }}</span>
                                        </td>
                                        <td>
                                            {{-- I-check kung may active schedule ang student from observations (exclude resolved) --}}
                                            @php 
                                                $activeSched = $student->observations()
                                                    ->where('referred_to_councilor', true)
                                                    ->whereNotNull('scheduled_at')
                                                    ->where('counseling_status', '!=', 'resolved')
                                                    ->latest()
                                                    ->first(); 
                                            @endphp

                                            @if($activeSched && $activeSched->scheduled_at)
                                            <div class="text-primary fw-medium small d-flex align-items-center">
                                                <span class="material-symbols-rounded"
                                                    style="font-size: 18px;">calendar_month</span>
                                                {{-- Format: Dec 09, 2025 06:46 AM --}}
                                                {{ \Carbon\Carbon::parse($activeSched->scheduled_at)->format('M d, Y h:i
                                                A') }}
                                            </div>
                                            @else
                                            <span class="text-muted small">No schedule set</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('student.report', $student->id) }}"
                                                class="btn btn-sm btn-info text-white me-1">
                                                <span class="material-symbols-rounded"
                                                    style="font-size: 16px;">assessment</span> Report
                                            </a>
                                            
                                            {{-- Observation Button --}}
                                            @if($student->observations->count() > 0)
                                                <button type="button" class="btn btn-sm btn-secondary text-white me-1" disabled
                                                    title="Student already referred to counselor">
                                                    <span class="material-symbols-rounded"
                                                        style="font-size: 16px;">psychology</span> Already Referred
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-success text-white me-1" 
                                                    data-bs-toggle="modal" data-bs-target="#observationModal{{ $student->id }}">
                                                    <span class="material-symbols-rounded"
                                                        style="font-size: 16px;">psychology</span> Observation
                                                </button>
                                            @endif
                                            
                                            <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                data-bs-toggle="modal" data-bs-target="#editModal{{ $student->id }}">
                                                <span class="material-symbols-rounded" style="font-size: 16px;">edit</span> Edit
                                            </button>
                                            
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal{{ $student->id }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">No students found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </section>

            {{-- Observation Modals for each student --}}
            @foreach($students as $student)
                @if($student->observations->count() == 0)
                    @include('components.observation-modal', ['student' => $student])
                @endif
            @endforeach

            {{-- Edit Student Modals --}}
            @foreach($students as $student)
                <div class="modal fade" id="editModal{{ $student->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $student->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="editModalLabel{{ $student->id }}">
                                    <span class="material-symbols-rounded" style="font-size: 22px;">edit</span> Edit Student
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('student.update', $student->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="edit_student_id{{ $student->id }}" class="form-label">Student ID <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="edit_student_id{{ $student->id }}" name="student_id" value="{{ $student->student_id }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_full_name{{ $student->id }}" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="edit_full_name{{ $student->id }}" name="full_name" value="{{ $student->full_name }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_section{{ $student->id }}" class="form-label">Section <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="edit_section{{ $student->id }}" name="section" value="{{ $student->section }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_subject{{ $student->id }}" class="form-label">Subject <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="edit_subject{{ $student->id }}" name="subject" value="{{ $student->subject }}" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <span class="material-symbols-rounded" style="font-size: 18px;">save</span> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Delete Confirmation Modals for each student --}}
            @foreach($students as $student)
                <div class="modal fade" id="deleteModal{{ $student->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $student->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="deleteModalLabel{{ $student->id }}">
                                    <i class="fas fa-exclamation-triangle"></i> Confirm Delete
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-3">Are you sure you want to delete <strong>{{ $student->full_name }}</strong>?</p>
                                <p class="text-muted mb-3">This action cannot be undone. All grades and records for this student will be permanently deleted.</p>
                                
                                <div class="alert alert-warning">
                                    <strong>To confirm deletion, please type:</strong> <code>Confirm</code>
                                </div>
                                
                                <input type="text" class="form-control" id="deleteConfirmInput{{ $student->id }}" 
                                    placeholder="Type 'Confirm' to delete" autocomplete="off">
                                <small class="text-danger d-none" id="deleteError{{ $student->id }}">
                                    Please type "Confirm" exactly to proceed.
                                </small>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <form action="{{ route('student.destroy', $student->id) }}" method="POST" id="deleteForm{{ $student->id }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger" onclick="confirmDelete{{ $student->id }}()">
                                        Delete Student
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    function confirmDelete{{ $student->id }}() {
                        const input = document.getElementById('deleteConfirmInput{{ $student->id }}');
                        const error = document.getElementById('deleteError{{ $student->id }}');
                        const deleteForm = document.getElementById('deleteForm{{ $student->id }}');
                        
                        if (input.value === 'Confirm') {
                            deleteForm.submit();
                        } else {
                            error.classList.remove('d-none');
                            input.classList.add('is-invalid');
                            input.focus();
                        }
                    }
                    
                    // Clear error when user types
                    document.getElementById('deleteConfirmInput{{ $student->id }}').addEventListener('input', function() {
                        const error = document.getElementById('deleteError{{ $student->id }}');
                        error.classList.add('d-none');
                        this.classList.remove('is-invalid');
                    });
                    
                    // Clear input when modal is closed
                    document.getElementById('deleteModal{{ $student->id }}').addEventListener('hidden.bs.modal', function() {
                        const input = document.getElementById('deleteConfirmInput{{ $student->id }}');
                        const error = document.getElementById('deleteError{{ $student->id }}');
                        input.value = '';
                        error.classList.add('d-none');
                        input.classList.remove('is-invalid');
                    });
                </script>
            @endforeach

        </div>
    </div>

    {{-- Risk Assessment Export Modal --}}
    <div class="modal fade" id="riskAssessmentExportModal" tabindex="-1" aria-labelledby="riskAssessmentExportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="riskAssessmentExportModalLabel">
                        <span class="material-symbols-rounded">download</span>
                        Export Risk Assessment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('export.risk-assessments') }}" method="GET">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="studentSelect" class="form-label fw-bold">Select Student</label>
                            <select class="form-select" id="studentSelect" name="student_id">
                                <option value="">All Students with Risk Assessments</option>
                                @foreach($studentsWithRisk as $student)
                                    <option value="{{ $student->id }}">{{ $student->full_name }} - {{ $student->section }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Choose a specific student or export all students' risk assessments.</div>
                        </div>
                        <div class="mb-3">
                            <label for="formatSelect" class="form-label fw-bold">Export Format</label>
                            <select class="form-select" id="formatSelect" name="format" required>
                                <option value="pdf">PDF Document</option>
                                <option value="excel">Excel Spreadsheet</option>
                                <option value="word">Word Document</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <span class="material-symbols-rounded" style="font-size: 18px;">download</span>
                            Export
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-layouts.app>