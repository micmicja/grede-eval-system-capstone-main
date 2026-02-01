<x-layouts.app title="Teacher Dashboard">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">

                <div class="mb-4">
                    <a href={{route('Dashboard.teacher')}} class="btn btn-link text-decoration-none p-0 text-muted">
                        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                <i class="bi bi-sliders2-vertical text-primary fs-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Grade Weight Settings</h5>
                                <p class="text-muted small mb-0">Define how final grades are calculated</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        {{-- Success Message --}}
                        @if(session('success'))
                        <div class="alert alert-success border-0 shadow-sm mb-4">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            {{ session('success') }}
                        </div>
                        @endif

                        {{-- Validation Error --}}
                        @if ($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm mb-4">
                            <ul class="mb-0 small">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('teacher.settings.update') }}">
                            @csrf

                            <div class="row g-3">
                                {{-- Quiz --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-uppercase">Quiz</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-lg bg-light"
                                            name="quiz_weight" value="{{ old('quiz_weight', $settings->quiz_weight) }}"
                                            min="0" max="100" required>
                                        <span class="input-group-text bg-light border-start-0 text-muted">%</span>
                                    </div>
                                </div>

                                {{-- Exam --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-uppercase">Exam</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-lg bg-light"
                                            name="exam_weight" value="{{ old('exam_weight', $settings->exam_weight) }}"
                                            min="0" max="100" required>
                                        <span class="input-group-text bg-light border-start-0 text-muted">%</span>
                                    </div>
                                </div>

                                {{-- Activity --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-uppercase">Activity</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-lg bg-light"
                                            name="activity_weight"
                                            value="{{ old('activity_weight', $settings->activity_weight) }}" min="0"
                                            max="100" required>
                                        <span class="input-group-text bg-light border-start-0 text-muted">%</span>
                                    </div>
                                </div>

                                {{-- Project --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-uppercase">Project</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-lg bg-light"
                                            name="project_weight"
                                            value="{{ old('project_weight', $settings->project_weight) }}" min="0"
                                            max="100" required>
                                        <span class="input-group-text bg-light border-start-0 text-muted">%</span>
                                    </div>
                                </div>

                                {{-- Recitation --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-uppercase">Recitation</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-lg bg-light"
                                            name="recitation_weight"
                                            value="{{ old('recitation_weight', $settings->recitation_weight) }}" min="0"
                                            max="100" required>
                                        <span class="input-group-text bg-light border-start-0 text-muted">%</span>
                                    </div>
                                </div>

                                {{-- Recitation --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-uppercase">Attendance</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-lg bg-light"
                                            name="attendance_weight"
                                            value="{{ old('attendance_weight', $settings->attendance_weight) }}" min="0"
                                            max="100" required>
                                        <span class="input-group-text bg-light border-start-0 text-muted">%</span>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4 text-secondary opacity-25">

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="d-block small text-muted">Current Total:</span>
                                    <strong id="totalWeight" class="text-dark fs-5">0%</strong>
                                    <small id="totalStatus" class="d-block"></small>
                                </div>
                                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" id="submitBtn">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4 text-muted small">
                    Changes will apply to all current semester subjects.
                </div>

            </div>
        </div>
    </div>

    <script>
        // Calculate total weight in real-time
        const inputs = document.querySelectorAll('input[type="number"]');
        const totalWeightEl = document.getElementById('totalWeight');
        const totalStatusEl = document.getElementById('totalStatus');
        const submitBtn = document.getElementById('submitBtn');

        function calculateTotal() {
            let total = 0;
            inputs.forEach(input => {
                const value = parseInt(input.value) || 0;
                total += value;
            });

            totalWeightEl.textContent = total + '%';
            
            if (total === 100) {
                totalWeightEl.className = 'text-success fs-5 fw-bold';
                totalStatusEl.textContent = '✓ Perfect!';
                totalStatusEl.className = 'd-block text-success small';
                submitBtn.disabled = false;
            } else if (total < 100) {
                totalWeightEl.className = 'text-warning fs-5 fw-bold';
                totalStatusEl.textContent = 'Need ' + (100 - total) + '% more';
                totalStatusEl.className = 'd-block text-warning small';
                submitBtn.disabled = true;
            } else {
                totalWeightEl.className = 'text-danger fs-5 fw-bold';
                totalStatusEl.textContent = (total - 100) + '% over limit';
                totalStatusEl.className = 'd-block text-danger small';
                submitBtn.disabled = true;
            }
        }

        inputs.forEach(input => {
            input.addEventListener('input', calculateTotal);
        });

        // Calculate on page load
        calculateTotal();
    </script>
</x-layouts.app>