<x-layouts.app :title="'Attendance Report - ' . $student->full_name">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Attendance Report</h2>
                <p class="text-muted">Student: <strong>{{ $student->full_name }}</strong></p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('Dashboard.teacher') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Total Days</h5>
                        <h2 class="text-primary">{{ $totalDays }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Days Present</h5>
                        <h2 class="text-success">{{ $presentDays }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Days Absent</h5>
                        <h2 class="text-danger">{{ $totalDays - $presentDays }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Attendance %</h5>
                        <h2 class="text-info">{{ number_format($attendancePercentage, 1) }}%</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-light">
                <h5 class="mb-0">Attendance History</h5>
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
                                <td>{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
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
</x-layouts.app>
