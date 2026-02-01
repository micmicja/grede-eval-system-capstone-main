<x-layouts.app :title="'Exam Report - ' . $student->full_name">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Exam Report</h2>
                <p class="text-muted">Student: <strong>{{ $student->full_name }}</strong></p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('Dashboard.teacher') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
            </div>
        </div>

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
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($examRecords as $exam)
                            <tr>
                                <td>{{ $exam->activity_title }}</td>
                                <td>{{ \Carbon\Carbon::parse($exam->date_taken)->format('M d, Y') }}</td>
                                <td>{{ $exam->score }}/100</td>
                                <td>
                                    @if($exam->score >= 80)
                                        <span class="badge bg-success">Excellent</span>
                                    @elseif($exam->score >= 70)
                                        <span class="badge bg-info">Good</span>
                                    @elseif($exam->score >= 60)
                                        <span class="badge bg-warning">Fair</span>
                                    @else
                                        <span class="badge bg-danger">Needs Improvement</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No exam records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
