<x-layouts.app title="Create Quiz">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Quiz Score System</h2>
                <p class="text-muted">Step 1: Create a quiz and set total score</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('Dashboard.teacher') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
            </div>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Errors --}}
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row">
            <!-- Create Quiz Form -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Create New Quiz</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('quiz.create-quiz') }}" onsubmit="return validateForm()">
                            @csrf

                            <div class="mb-3">
                                <label for="quiz_name" class="form-label fw-bold">Quiz Name *</label>
                                <input type="text" 
                                       class="form-control @error('quiz_name') is-invalid @enderror" 
                                       id="quiz_name" 
                                       name="quiz_name" 
                                       placeholder="e.g., Chapter 5 Quiz, Midterm Quiz"
                                       value="{{ old('quiz_name') }}" 
                                       required>
                                @error('quiz_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="date_taken" class="form-label fw-bold">Date Taken *</label>
                                <input type="date" 
                                       class="form-control @error('date_taken') is-invalid @enderror"
                                       id="date_taken" 
                                       name="date_taken"
                                       value="{{ old('date_taken', now()->format('Y-m-d')) }}" 
                                       required>
                                @error('date_taken')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="total_items" class="form-label fw-bold">Total Quiz Score *</label>
                                <input type="number"
                                       class="form-control @error('total_items') is-invalid @enderror"
                                       id="total_items" 
                                       name="total_items" 
                                       min="1" 
                                       max="1000"
                                       placeholder="e.g., 20, 50, 100"
                                       value="{{ old('total_items', 20) }}" 
                                       required>
                                <small class="text-muted">Enter the maximum score for this quiz</small>
                                @error('total_items')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-arrow-right"></i> Create Quiz & Add Scores
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Recent Quizzes -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Recent Quizzes</h5>
                    </div>
                    <div class="card-body">
                        @if(isset($recentQuizzes) && $recentQuizzes->count() > 0)
                        <div class="list-group">
                            @foreach($recentQuizzes as $quiz)
                            <a href="{{ route('quiz.add-scores', ['quiz_name' => $quiz->activity_title, 'date_taken' => $quiz->date_taken, 'total_items' => $quiz->total_score]) }}" 
                               class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $quiz->activity_title }}</h6>
                                    <small>{{ \Carbon\Carbon::parse($quiz->date_taken)->format('M d, Y') }}</small>
                                </div>
                                <p class="mb-1">
                                    <small class="text-muted">Total: {{ $quiz->total_score }} points</small>
                                </p>
                                <small class="text-primary">Click to add/edit scores →</small>
                            </a>
                            @endforeach
                        </div>
                        @else
                        <p class="text-muted text-center py-4">No quizzes created yet. Create your first quiz above!</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
