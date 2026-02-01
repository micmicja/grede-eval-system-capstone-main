<x-layouts.app title="Counselor Dashboard">
    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Counselor Portal</h4>
                <p class="text-muted small mb-0">Manage student referrals and schedules</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-white shadow-sm rounded-pill px-4 border">
                    <div class="d-flex align-items-center text-danger fw-semibold">
                        <span class="material-symbols-rounded me-2" style="font-size: 20px;">logout</span>
                        Logout
                    </div>
                </button>
            </form>
        </div>



        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-danger text-white rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="opacity-75">High Priority Cases</h6>
                                <h2 class="fw-bold mb-0">{{ $stats['high_priority'] }}</h2>
                            </div>
                            <span class="material-symbols-rounded" style="font-size: 48px;">priority_high</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-warning text-dark rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="opacity-75">Ongoing Sessions</h6>
                                <h2 class="fw-bold mb-0">{{ $stats['ongoing'] }}</h2>
                            </div>
                            <span class="material-symbols-rounded" style="font-size: 48px;">event_repeat</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-success text-white rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="opacity-75">Resolved This Month</h6>
                                <h2 class="fw-bold mb-0">{{ $stats['resolved'] }}</h2>
                            </div>
                            <span class="material-symbols-rounded" style="font-size: 48px;">task_alt</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-primary text-white rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="opacity-75">At-Risk Students</h6>
                                <h2 class="fw-bold mb-0">{{ $stats['at_risk_students'] }}</h2>
                            </div>
                            <span class="material-symbols-rounded" style="font-size: 48px;">warning</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
        <div class="alert alert-danger rounded-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (session('success'))
        <div class="alert alert-success rounded-4">
            {{ session('success') }}
        </div>
        @endif

        <div class="row g-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="fw-bold mb-0">Instructors List</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-2">
                            @forelse($instructors as $instructor)
                            <div
                                class="d-flex justify-content-between align-items-center bg-light p-3 rounded-pill border">
                                <div class="d-flex align-items-center">
                                    <span class="material-symbols-rounded me-2 text-secondary"
                                        style="font-size: 20px;">person</span>
                                    <span class="small fw-semibold text-dark">{{ $instructor->full_name }}</span>
                                </div>
                                <span class="badge bg-white text-danger border rounded-pill px-3 shadow-sm">
                                    {{ $instructor->flag_created_count }}
                                </span>
                            </div>
                            @empty
                            <p class="text-center text-muted small">No instructors found.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center mb-0">
                            <h5 class="fw-bold mb-0">Student Counseling Referrals</h5>
                            <form method="GET" action="{{ route('councilorDashboard.view') }}" class="d-flex gap-2">
                                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm rounded-pill px-3"
                                    placeholder="Search student or reason..." style="width: 220px;">

                                <select name="teacher_id" class="form-select form-select-sm rounded-pill" style="width: 180px;">
                                    <option value="">All Teachers</option>
                                    @foreach($instructors as $ins)
                                        <option value="{{ $ins->id }}" {{ request('teacher_id') == $ins->id ? 'selected' : '' }}>{{ $ins->full_name }} ({{ $ins->flag_created_count }})</option>
                                    @endforeach
                                </select>

                                <select name="status" class="form-select form-select-sm rounded-pill" style="width: 150px;">
                                    <option value="">Any Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                </select>

                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3">Apply</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body px-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light text-uppercase">
                                    <tr style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                        <th class="ps-4">Student & Section</th>
                                        <th>Referred By</th>
                                        <th>Reason</th>
                                        <th>Urgency</th>
                                        <th>Schedule</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Show Risk Observations First --}}
                                    @foreach($riskObservations as $observation)
                                    <tr class="table-warning">
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $observation->student->full_name }}</div>
                                            <div class="small text-muted text-lowercase">{{ $observation->student->section }}</div>
                                        </td>
                                        <td><span class="small fw-medium">{{ $observation->teacher->full_name }}</span></td>
                                        <td>
                                            @php
                                                $behaviors = is_array($observation->observed_behaviors) 
                                                    ? $observation->observed_behaviors 
                                                    : json_decode($observation->observed_behaviors, true) ?? [];
                                                $behaviorText = count($behaviors) > 0 ? implode(', ', array_slice($behaviors, 0, 2)) : 'Risk Assessment';
                                                if(count($behaviors) > 2) $behaviorText .= '...';
                                            @endphp
                                            <span class="text-truncate d-inline-block" style="max-width: 150px;"
                                                title="{{ implode(', ', $behaviors) }}">
                                                {{ $behaviorText }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill px-3 py-2 {{ $observation->risk_status === 'High Risk' ? 'bg-danger text-white' : 'bg-warning text-dark' }}"
                                                style="font-size: 11px;">
                                                {{ $observation->risk_status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold {{ $observation->scheduled_at ? 'text-primary' : 'text-muted' }} d-flex align-items-center">
                                                <span class="material-symbols-rounded me-1"
                                                    style="font-size: 16px;">calendar_month</span>
                                                {{ $observation->scheduled_at ? $observation->scheduled_at->format('M d, Y h:i A') : 'Not Scheduled' }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#scheduleRiskModal{{ $observation->id }}">
                                                    <span class="material-symbols-rounded align-middle"
                                                        style="font-size: 16px;">event</span> Schedule
                                                </button>
                                                <button
                                                    class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm"
                                                    data-bs-toggle="modal" data-bs-target="#detailRiskModal{{ $observation->id }}">
                                                    <span class="material-symbols-rounded align-middle"
                                                        style="font-size: 16px;">visibility</span> Details
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- Detail Modal for Risk Observation --}}
                                    <div class="modal fade" id="detailRiskModal{{ $observation->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow rounded-4">
                                                <div class="modal-header border-0">
                                                    <h5 class="fw-bold mb-0">Risk Assessment Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body py-0">
                                                    <div class="mb-3">
                                                        <p class="small text-muted mb-1">Student:</p>
                                                        <p class="fw-bold mb-0">{{ $observation->student->full_name }}</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <p class="small text-muted mb-1">Section:</p>
                                                        <p class="fw-bold mb-0">{{ $observation->student->section }}</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <p class="small text-muted mb-1">Risk Level:</p>
                                                        <span class="badge {{ $observation->risk_status === 'High Risk' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                                            {{ $observation->risk_status }}
                                                        </span>
                                                    </div>
                                                    <div class="mb-3">
                                                        <p class="small text-muted mb-1">Calculated Average:</p>
                                                        <p class="fw-bold mb-0">{{ number_format($observation->calculated_average, 2) }}%</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="small fw-bold text-dark mb-1">Observed Behaviors</label>
                                                        <ul class="list-group">
                                                            @foreach($behaviors as $behavior)
                                                                <li class="list-group-item">{{ $behavior }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <div class="mb-3">
                                                        <p class="small text-muted mb-1">Referred By:</p>
                                                        <p class="fw-bold mb-0">{{ $observation->teacher->full_name }}</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <p class="small text-muted mb-1">Date Observed:</p>
                                                        <p class="fw-bold mb-0">{{ $observation->created_at->format('M d, Y h:i A') }}</p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Schedule Modal for Risk Observation --}}
                                    <div class="modal fade" id="scheduleRiskModal{{ $observation->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow rounded-4">
                                                <form action="{{ route('councilor.scheduleRiskObservation', $observation->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header border-0">
                                                        <h5 class="fw-bold mb-0">Set Counseling Schedule</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="small text-muted">Student: <strong>{{ $observation->student->full_name }}</strong></p>
                                                        <p class="small text-muted">Risk Level: 
                                                            <span class="badge {{ $observation->risk_status === 'High Risk' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                                                {{ $observation->risk_status }}
                                                            </span>
                                                        </p>
                                                        <label class="form-label small fw-bold">Select Date & Time</label>
                                                        <input type="datetime-local" name="scheduled_at"
                                                            class="form-control rounded-pill mb-3" required>

                                                        <h6 class="small fw-bold text-danger mt-3">Already Booked Slots:</h6>
                                                        <ul class="list-group list-group-flush border rounded-3 overflow-auto small"
                                                            style="max-height: 120px;">
                                                            @foreach($evaluations as $booked)
                                                            @if($booked->scheduled_at && $booked->status !== 'resolved')
                                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                <span>{{ $booked->scheduled_at->format('M d, h:i A') }}</span>
                                                                <span class="badge bg-light text-primary rounded-pill border shadow-sm">Occupied</span>
                                                            </li>
                                                            @endif
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="submit" class="btn btn-primary w-100 rounded-pill">Save Schedule</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach

                                    {{-- Show Regular Evaluations --}}
                                    @forelse($evaluations as $eval)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $eval->student->full_name ?? "Student
                                                Name" }}</div>
                                            <div class="small text-muted text-lowercase">{{ $eval->student->section ??
                                                'section unknown' }}</div>
                                        </td>
                                        <td><span class="small fw-medium">{{ $eval->teacher->full_name ?? "Teacher Name"
                                                }}</span></td>
                                        <td>
                                            <span class="text-truncate d-inline-block" style="max-width: 150px;"
                                                title="{{ $eval->comments }}">
                                                {{ $eval->comments }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge rounded-pill px-3 py-2 {{ $eval->urgency == 'high' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning' }}"
                                                style="font-size: 11px;">
                                                {{ ucfirst($eval->urgency ?? 'Moderate') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold text-primary d-flex align-items-center">
                                                <span class="material-symbols-rounded me-1"
                                                    style="font-size: 16px;">calendar_month</span>
                                                {{ $eval->scheduled_at ? $eval->scheduled_at->format('M d, Y h:i A') :
                                                'Not Set' }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#scheduleModal{{ $eval->id }}">
                                                    <span class="material-symbols-rounded align-middle"
                                                        style="font-size: 16px;">event</span> Schedule
                                                </button>
                                                <button
                                                    class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm"
                                                    data-bs-toggle="modal" data-bs-target="#detailModal{{ $eval->id }}">
                                                    <span class="material-symbols-rounded align-middle"
                                                        style="font-size: 16px;">visibility</span> Details
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="detailModal{{ $eval->id }}" tabindex="-1"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow rounded-4">
                                                <form action="{{ route('councilor.updateStatus', $eval->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-header border-0">
                                                        <h5 class="fw-bold mb-0">Referral Details</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body py-0">
                                                        <div class="mb-3">
                                                            <p class="small text-muted mb-1">Scheduling for:</p>
                                                            <p class="fw-bold mb-0">{{ $eval->student->full_name }}</p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="small fw-bold text-dark mb-1">Full Reason /
                                                                Factors</label>
                                                            <div class="p-3 bg-light rounded-3 border small"
                                                                style="white-space: pre-wrap; min-height: 80px;">{{
                                                                $eval->comments }}</div>
                                                        </div>
                                                        <div class="mb-4">
                                                            <label class="small fw-bold text-primary mb-1">Set
                                                                Resolution Status</label>
                                                            <select name="status" class="form-select rounded-pill">
                                                                <option value="pending" {{ $eval->status == 'pending' ?
                                                                    'selected' : '' }}>Pending</option>
                                                                <option value="ongoing" {{ $eval->status == 'ongoing' ?
                                                                    'selected' : '' }}>Ongoing</option>
                                                                <option value="resolved" {{ $eval->status == 'resolved'
                                                                    ? 'selected' : '' }}>Resolved</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="submit"
                                                            class="btn btn-primary w-100 rounded-pill py-2">Save Status
                                                            Update</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="scheduleModal{{ $eval->id }}" tabindex="-1"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow rounded-4">
                                                <form action="{{ route('councilor.setSchedule', $eval->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    <div class="modal-header border-0">
                                                        <h5 class="fw-bold mb-0">Set Schedule</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="small text-muted">Student: <strong>{{
                                                                $eval->student->full_name }}</strong></p>
                                                        <label class="form-label small fw-bold">Select Date &
                                                            Time</label>
                                                        <input type="datetime-local" name="scheduled_at"
                                                            class="form-control rounded-pill mb-3" required>

                                                        <h6 class="small fw-bold text-danger mt-3">Already Booked Slots:
                                                        </h6>
                                                        <ul class="list-group list-group-flush border rounded-3 overflow-auto small"
                                                            style="max-height: 120px;">
                                                            @foreach($evaluations as $booked)
                                                            {{-- Ipakita lang ang may schedule at HINDI pa resolved --}}
                                                            @if($booked->scheduled_at && $booked->status !== 'resolved')
                                                            <li
                                                                class="list-group-item d-flex justify-content-between align-items-center">
                                                                <span>{{ $booked->scheduled_at->format('M d, h:i A')
                                                                    }}</span>
                                                                <span
                                                                    class="badge bg-light text-primary rounded-pill border shadow-sm">Occupied</span>
                                                            </li>
                                                            @endif
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="submit"
                                                            class="btn btn-primary w-100 rounded-pill">Save
                                                            Schedule</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                        @if($riskObservations->count() == 0)
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <span class="material-symbols-rounded d-block mb-2"
                                                    style="font-size: 48px;">folder_open</span>
                                                No referrals found.
                                            </td>
                                        </tr>
                                        @endif
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>