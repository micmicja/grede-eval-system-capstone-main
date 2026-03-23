<x-layouts.app title="Create Department">

    @push('head')
    <style>
        .form-wrapper {
            background: #ffffff;
            border-radius: 18px;
            padding: 2rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }

        .page-title {
            font-weight: 700;
            color: #1e293b;
        }

        .form-label {
            font-weight: 600;
        }

        .form-control {
            border-radius: .65rem;
        }

        .btn {
            border-radius: .65rem;
        }

        .major-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .major-item input {
            flex: 1;
        }
    </style>
    @endpush

    <div class="container mt-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <a href="{{ route('Dashboard.admin', ['tab' => 'departments']) }}" class="btn btn-outline-secondary">
                ← Back to Dashboard
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-wrapper">
            <h3 class="page-title mb-4">Add New Department</h3>

            <form method="POST" action="{{ route('admin.department.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Department Code</label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror"
                               name="code" value="{{ old('code') }}" required
                               placeholder="e.g., BSCS">
                        <small class="form-text text-muted">Short code for the department (max 10 characters)</small>
                        @error('code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Department Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               name="name" value="{{ old('name') }}" required
                               placeholder="e.g., Bachelor of Science in Computer Science">
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mt-4">
                        <label class="form-label">Majors (Optional)</label>
                        <small class="form-text text-muted d-block mb-2">Add majors for this department. Leave empty if no majors are needed.</small>

                        <div id="majors-container">
                            <!-- Dynamic major fields will be added here -->
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-major-btn">
                            + Add Major
                        </button>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <a href="{{ route('Dashboard.admin', ['tab' => 'departments']) }}" class="btn btn-outline-secondary px-4">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        Create Department
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('majors-container');
            const addBtn = document.getElementById('add-major-btn');
            let majorIndex = 0;

            function addMajorField(value = '') {
                const div = document.createElement('div');
                div.className = 'major-item';
                div.innerHTML = `
                    <input type="text" class="form-control" name="majors[]" value="${value}" placeholder="Enter major name">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-major-btn">Remove</button>
                `;
                container.appendChild(div);

                div.querySelector('.remove-major-btn').addEventListener('click', function() {
                    div.remove();
                });

                majorIndex++;
            }

            addBtn.addEventListener('click', function() {
                addMajorField();
            });

            // Restore old values if validation failed
            @if(old('majors'))
                @foreach(old('majors') as $major)
                    @if($major)
                        addMajorField('{{ $major }}');
                    @endif
                @endforeach
            @endif
        });
    </script>

</x-layouts.app>
