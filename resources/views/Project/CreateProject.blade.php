<x-layouts.app title="Create Project">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Project Score System</h2>
                <p class="text-muted">Step 1: Create a project and set total score</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('Dashboard.teacher') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

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
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Create New Project</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('project.create-project') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="project_name" class="form-label fw-bold">Project Name *</label>
                                <input type="text" 
                                       class="form-control @error('project_name') is-invalid @enderror" 
                                       id="project_name" 
                                       name="project_name" 
                                       placeholder="e.g., Capstone Project, Research Paper"
                                       value="{{ old('project_name') }}" 
                                       required>
                                @error('project_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="date_taken" class="form-label fw-bold">Date Submitted *</label>
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
                                <label for="total_items" class="form-label fw-bold">Total Project Score *</label>
                                <input type="number"
                                       class="form-control @error('total_items') is-invalid @enderror"
                                       id="total_items" 
                                       name="total_items" 
                                       min="1" 
                                       max="1000"
                                       placeholder="e.g., 50, 100, 200"
                                       value="{{ old('total_items', 100) }}" 
                                       required>
                                <small class="text-muted">Enter the maximum score for this project</small>
                                @error('total_items')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-arrow-right"></i> Create Project & Add Scores
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Recent Projects</h5>
                    </div>
                    <div class="card-body">
                        @if(isset($recentProjects) && $recentProjects->count() > 0)
                        <div class="list-group">
                            @foreach($recentProjects as $project)
                            <a href="{{ route('project.add-scores', ['project_name' => $project->activity_title, 'date_taken' => $project->date_taken, 'total_items' => $project->total_score]) }}" 
                               class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $project->activity_title }}</h6>
                                    <small>{{ \Carbon\Carbon::parse($project->date_taken)->format('M d, Y') }}</small>
                                </div>
                                <p class="mb-1">
                                    <small class="text-muted">Total: {{ $project->total_score }} points</small>
                                </p>
                                <small class="text-primary">Click to add/edit scores →</small>
                            </a>
                            @endforeach
                        </div>
                        @else
                        <p class="text-muted text-center py-4">No projects created yet. Create your first project above!</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
