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
                        @if ($percentage === null)
                        <span class="text-danger small">Please set grade allocation in settings.</span>

                        @else
                        <a href="{{ route('add-student') }}" class="btn btn-sm btn-outline-primary">
                            <span class="material-symbols-rounded" style="font-size: 18px;">person_add</span>
                            Add Student
                        </a>
                        @endif


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
                                            {{-- I-check kung may schedule ang student --}}
                                            @php $activeSched = $student->evaluations->first(); @endphp

                                            @if($activeSched)
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
                                            <button type="button" class="btn btn-sm btn-success text-white me-1" 
                                                data-bs-toggle="modal" data-bs-target="#observationModal{{ $student->id }}">
                                                <span class="material-symbols-rounded"
                                                    style="font-size: 16px;">psychology</span> Observation
                                            </button>
                                            
                                            <form action="{{ route('student.destroy', $student->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this student?');">
                                                    Delete
                                                </button>
                                            </form>
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
                @include('components.observation-modal', ['student' => $student])
            @endforeach

        </div>
    </div>


</x-layouts.app>