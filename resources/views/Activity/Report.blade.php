<x-layouts.app :title="'Activity Report - ' . $student->full_name">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Activity Report</h2>
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
                        <h5 class="card-title">Total Activities</h5>
                        <h2 class="text-primary">{{ $activityRecords->count() }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Average Score</h5>
                        <h2 class="text-success">{{ number_format($averageActivityScore, 1) }}/100</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Highest Score</h5>
                        <h2 class="text-info">{{ $highestActivityScore }}/100</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Lowest Score</h5>
                        <h2 class="text-warning">{{ $lowestActivityScore }}/100</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-light">
                <h5 class="mb-0">Activity History</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Activity Title</th>
                            <th>Date Taken</th>
                            <th>Score</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activityRecords as $record)
                            <tr>
                                <td>{{ $record->activity_title }}</td>
                                <td>{{ \Carbon\Carbon::parse($record->date_taken)->format('M d, Y') }}</td>
                                <td>{{ $record->score }}/100</td>
                                <td>
                                    @if($record->score >= 80)
                                        <span class="badge bg-success">Excellent</span>
                                    @elseif($record->score >= 70)
                                        <span class="badge bg-info">Good</span>
                                    @elseif($record->score >= 60)
                                        <span class="badge bg-warning">Fair</span>
                                    @else
                                        <span class="badge bg-danger">Needs Improvement</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No activity records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
