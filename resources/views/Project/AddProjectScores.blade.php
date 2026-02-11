<x-layouts.app title="Add Project Scores">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Project Score System</h2>
                <p class="text-muted">Step 2: Add scores for all students</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group me-2" role="group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('export.activity-results', ['type' => 'Project', 'title' => $projectName, 'date' => $dateTaken, 'format' => 'pdf']) }}"><i class="fas fa-file-pdf"></i> Export as PDF</a></li>
                        <li><a class="dropdown-item" href="{{ route('export.activity-results', ['type' => 'Project', 'title' => $projectName, 'date' => $dateTaken, 'format' => 'excel']) }}"><i class="fas fa-file-excel"></i> Export as Excel</a></li>
                        <li><a class="dropdown-item" href="{{ route('export.activity-results', ['type' => 'Project', 'title' => $projectName, 'date' => $dateTaken, 'format' => 'word']) }}"><i class="fas fa-file-word"></i> Export as Word</a></li>
                    </ul>
                </div>
                <a href="{{ route('project.create-project-page') }}" class="btn btn-outline-secondary">Back to Project List</a>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body bg-light">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Project Name:</strong> {{ $projectName }}
                    </div>
                    <div class="col-md-3">
                        <strong>Date:</strong> {{ \Carbon\Carbon::parse($dateTaken)->format('F d, Y') }}
                    </div>
                    <div class="col-md-2">
                        <strong>Total Score:</strong> /{{ $totalItems }}
                    </div>
                    <div class="col-md-4">
                        <form method="GET" action="{{ route('project.add-scores') }}" class="d-flex align-items-center">
                            <input type="hidden" name="project_name" value="{{ $projectName }}">
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

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Enter Scores for All Students</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('project.save-scores') }}">
                    @csrf
                    <input type="hidden" name="project_name" value="{{ $projectName }}">
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
                                @foreach($students as $student)
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
                                Leave blank for students who didn't submit the project
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
