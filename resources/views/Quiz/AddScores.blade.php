<x-layouts.app title="Add Quiz Scores">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Quiz Score System</h2>
                <p class="text-muted">Step 2: Add scores for all students</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('quiz.create-quiz-page') }}" class="btn btn-outline-secondary">Back to Quiz List</a>
            </div>
        </div>

        {{-- Quiz Information Card --}}
        <div class="card shadow mb-4">
            <div class="card-body bg-light">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Quiz Name:</strong> {{ $quizName }}
                    </div>
                    <div class="col-md-3">
                        <strong>Date:</strong> {{ \Carbon\Carbon::parse($dateTaken)->format('F d, Y') }}
                    </div>
                    <div class="col-md-2">
                        <strong>Total Score:</strong> /{{ $totalItems }}
                    </div>
                    <div class="col-md-4">
                        <form method="GET" action="{{ route('quiz.add-scores') }}" class="d-flex align-items-center">
                            <input type="hidden" name="quiz_name" value="{{ $quizName }}">
                            <input type="hidden" name="date_taken" value="{{ $dateTaken }}">
                            <input type="hidden" name="total_items" value="{{ $totalItems }}">
                            <label class="me-2 mb-0"><strong>Filter Section:</strong></label>
                            <select name="section" class="form-select form-select-sm" onchange="this.form.submit()" style="max-width: 200px;">
                                <option value="">All Sections</option>
                                @foreach($sections as $section)
                                <option value="{{ $section }}" {{ $selectedSection == $section ? 'selected' : '' }}>
                                    {{ $section }}
                                </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Student Scores Form --}}
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Enter Scores for All Students</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('quiz.save-scores') }}" id="scoresForm">
                    @csrf
                    <input type="hidden" name="quiz_name" value="{{ $quizName }}">
                    <input type="hidden" name="date_taken" value="{{ $dateTaken }}">
                    <input type="hidden" name="total_items" value="{{ $totalItems }}">
                    <input type="hidden" name="selected_section" value="{{ $selectedSection }}">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40%;">Student Name</th>
                                    <th style="width: 25%;">Section</th>
                                    <th style="width: 35%;">Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $index => $student)
                                <tr>
                                    <td class="fw-bold">{{ $student->full_name }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $student->section }}</span>
                                    </td>
                                    <td>
                                        <div class="input-group" style="max-width: 200px;">
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="scores[{{ $student->full_name }}]" 
                                                   min="0" 
                                                   max="{{ $totalItems }}"
                                                   value="{{ $existingScores[$student->full_name] ?? '' }}"
                                                   placeholder="0">
                                            <span class="input-group-text">/{{ $totalItems }}</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            <small>
                                <i class="fas fa-info-circle"></i> 
                                Leave blank for students who didn't take the quiz
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-save"></i> Save All Scores
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('scoresForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    console.log('Form loaded:', form);
    
    form.addEventListener('submit', function(e) {
        console.log('Form submitting...');
        console.log('Action:', form.action);
        console.log('Method:', form.method);
        
        // Check if at least one score is entered
        const scoreInputs = form.querySelectorAll('input[name^="scores"]');
        let hasScore = false;
        scoreInputs.forEach(input => {
            if (input.value && input.value.trim() !== '') {
                hasScore = true;
            }
        });
        
        if (!hasScore) {
            e.preventDefault();
            alert('Please enter at least one score before saving.');
            return false;
        }
        
        // Disable button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    });
});
</script>
