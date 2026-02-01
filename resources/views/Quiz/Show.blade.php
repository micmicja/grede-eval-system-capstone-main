<x-layouts.app title="Record Quiz Scores">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Record Quiz Scores</h2>
                <p class="text-muted">Add or update quiz scores for your students</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('Dashboard.teacher') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
            </div>
        </div>

        {{-- Display success message --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        {{-- Display errors --}}
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="row">
            <!-- Quiz Form Card -->
            <div class="col-lg-5 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Add New Quiz Score</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('quiz.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="full_name" class="form-label fw-bold">Student Name *</label>
                                <select class="form-select @error('full_name') is-invalid @enderror" id="full_name"
                                    name="full_name" required>
                                    <option value="">-- Select Student --</option>
                                    @foreach($students as $student)
                                    <option value="{{ $student->full_name }}" {{ old('full_name')==$student->full_name ?
                                        'selected' : '' }}>
                                        {{ $student->full_name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="activity_title" class="form-label fw-bold">Quiz Title *</label>
                                <input type="text" class="form-control @error('activity_title') is-invalid @enderror"
                                    id="activity_title" name="activity_title" placeholder="e.g., Chapter 5 Quiz"
                                    value="{{ old('activity_title') }}" required>
                                @error('activity_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="date_taken" class="form-label fw-bold">Date Taken *</label>
                                <input type="date" class="form-control @error('date_taken') is-invalid @enderror"
                                    id="date_taken" name="date_taken"
                                    value="{{ old('date_taken', now()->format('Y-m-d')) }}" required>
                                @error('date_taken')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="total_items" class="form-label fw-bold">Total Items *</label>
                                <input type="number"
                                    class="form-control mb-2 @error('total_items') is-invalid @enderror"
                                    id="total_items" name="total_items" min="1" max="1000"
                                    value="{{ old('total_items', 10) }}" required>
                                @error('total_items')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <label for="score" class="form-label fw-bold">Correct Answers (0 - selected total)
                                    *</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('score') is-invalid @enderror"
                                        id="score" name="score" min="0" value="{{ old('score') }}" required>
                                    <span class="input-group-text" id="totalLabel">/ {{ old('total_items', 10) }}</span>
                                </div>
                                <small class="form-text text-muted">Enter number of correct answers. Percentage will be
                                    calculated automatically.</small>
                                <div class="mt-2">
                                    <strong>Preview:</strong>
                                    <span id="scorePreview">0 / 100 (0%)</span>
                                </div>
                                @error('score')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save"></i> Save Quiz Score
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Quiz Records List -->
            <div class="col-lg-7 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Recent Quiz Records</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Quiz Title</th>
                                    <th>Date</th>
                                    <th>Score</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teacher->quiz_exam_activity()->where('activity_type',
                                'quiz')->orderBy('date_taken', 'desc')->limit(10)->get() as $quiz)
                                <tr>
                                    <td>{{ $quiz->full_name }}</td>
                                    <td>{{ $quiz->activity_title }}</td>
                                    <td>{{ \Carbon\Carbon::parse($quiz->date_taken)->format('M d, Y') }}</td>
                                    <td>
                                        @if($quiz->score >= 80)
                                        <span class="badge bg-success">{{ $quiz->score }}/100</span>
                                        @elseif($quiz->score >= 60)
                                        <span class="badge bg-warning">{{ $quiz->score }}/100</span>
                                        @else
                                        <span class="badge bg-danger">{{ $quiz->score }}/100</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">

                                            <!-- Update Button -->
                                            <a href="{{ route('quiz.edit', $quiz->id) }}"
                                                class="btn btn-sm btn-outline-primary d-flex align-items-center px-3">
                                                <span class="material-symbols-rounded me-1" style="font-size:18px;">
                                                    edit
                                                </span>
                                                Update
                                            </a>

                                            <!-- Delete Button -->
                                            <form action="{{ route('quiz.destroy', $quiz->id) }}" method="POST"
                                                onsubmit="return confirm('Delete this quiz record?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-danger d-flex align-items-center px-3">
                                                    <span class="material-symbols-rounded me-1" style="font-size:18px;">
                                                        delete
                                                    </span>
                                                    Delete
                                                </button>
                                            </form>

                                        </div>

                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No quiz records found. Add one to get started.
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
</x-layouts.app>

<script>
    (function(){
        const totalInput = document.getElementById('total_items');
        const scoreInput = document.getElementById('score');
        const totalLabel = document.getElementById('totalLabel');
        const preview = document.getElementById('scorePreview');

        function updatePreview(){
            const total = parseFloat(totalInput.value) || 0;
            const correct = parseFloat(scoreInput.value) || 0;
            const percent = total > 0 ? ((correct / total) * 100) : 0;
            const percentStr = Math.round(percent * 10) / 10;
            totalLabel.textContent = '/ ' + (total || 0);
            preview.textContent = `${percentStr} % (${correct}/${total})`;
        }

        if(totalInput && scoreInput){
            totalInput.addEventListener('input', function(){
                // adjust max attribute for score input
                const total = parseInt(this.value) || 0;
                scoreInput.max = total;
                updatePreview();
            });

            scoreInput.addEventListener('input', updatePreview);

            // initialize
            updatePreview();
        }
    })();
</script>