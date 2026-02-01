<x-layouts.app title="Edit Recitation">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Edit Recitation</h2>
                <p class="text-muted">Update recitation information for {{ $rec->full_name }}</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('Dashboard.teacher') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
            </div>
        </div>

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

        <div class="card shadow" style="max-width: 600px;">
            <div class="card-body p-5">
                <form method="POST" action="{{ route('recitation.update', $rec->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold">Student Name</label>
                        <input type="text" class="form-control" value="{{ $rec->full_name }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="activity_title" class="form-label fw-bold">Recitation Topic *</label>
                        <input type="text" 
                               class="form-control @error('activity_title') is-invalid @enderror" 
                               id="activity_title" 
                               name="activity_title"
                               value="{{ old('activity_title', $rec->activity_title) }}"
                               required>
                        @error('activity_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="date_taken" class="form-label fw-bold">Date Conducted *</label>
                        <input type="date" 
                               class="form-control @error('date_taken') is-invalid @enderror" 
                               id="date_taken" 
                               name="date_taken"
                               value="{{ old('date_taken', $rec->date_taken) }}"
                               required>
                        @error('date_taken')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="total_items" class="form-label fw-bold">Total Items (optional)</label>
                        <input type="number"
                               class="form-control mb-2 @error('total_items') is-invalid @enderror"
                               id="total_items"
                               name="total_items"
                               min="1"
                               max="1000"
                               value="{{ old('total_items') }}"
                               placeholder="e.g., 10">
                        @error('total_items')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <label for="score" class="form-label fw-bold">Score *</label>
                        <div class="input-group">
                            <input type="number"
                                   class="form-control @error('score') is-invalid @enderror"
                                   id="score"
                                   name="score"
                                   min="0"
                                   step="0.5"
                                   value="{{ old('score', $rec->score) }}"
                                   required>
                            <span class="input-group-text" id="totalLabel">/100</span>
                        </div>
                        <small class="form-text text-muted">If you provide Total Items above, enter the number of correct answers and the system will recalculate percentage on save. Otherwise enter percentage (0-100).</small>
                        @error('score')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('Dashboard.teacher') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Recitation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>

<script>
    (function(){
        const totalInput = document.getElementById('total_items');
        const scoreInput = document.getElementById('score');
        const totalLabel = document.getElementById('totalLabel');

        function updateLabel(){
            const total = parseFloat(totalInput.value) || 0;
            totalLabel.textContent = total > 0 ? ('/ ' + total) : '/100';
        }

        if(totalInput && scoreInput){
            totalInput.addEventListener('input', updateLabel);
            // initialize
            updateLabel();
        }
    })();
</script>