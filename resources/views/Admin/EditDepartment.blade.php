<x-layouts.app title="Edit Department">

    @push('head')
    <style>
        .form-wrapper { background: #fff; border-radius: 18px; padding: 2rem; border: 1px solid #e5e7eb; box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
        .page-title { font-weight:700; color:#1e293b; }
        .form-label { font-weight:600; }

        .major-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }

        .major-item input {
            flex: 1;
        }

        .existing-major {
            display: flex;
            gap: 10px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
            margin-bottom: 10px;
            align-items: center;
        }

        .existing-major span {
            flex: 1;
            font-weight: 500;
        }
    </style>
    @endpush

    <div class="container mt-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <a href="{{ route('Dashboard.admin', ['tab' => 'departments']) }}" class="btn btn-outline-secondary">← Back to Dashboard</a>
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
            <h3 class="page-title mb-4">Edit Department</h3>

            <form method="POST" action="{{ route('admin.department.update', $department->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Department Code</label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror"
                               name="code" value="{{ old('code', $department->code) }}" required>
                        <small class="form-text text-muted">Short code for the department (max 10 characters)</small>
                        @error('code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Department Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               name="name" value="{{ old('name', $department->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mt-4">
                        <label class="form-label">Majors</label>
                        <small class="form-text text-muted d-block mb-2">Manage majors for this department.</small>

                        <!-- Existing Majors -->
                        @if($department->majors && $department->majors->count() > 0)
                            <div class="mb-3">
                                <label class="form-label mb-2" style="display: block;">Existing Majors:</label>
                                @foreach($department->majors as $major)
                                    <div class="existing-major">
                                        <span>{{ $major->name }}</span>
                                        <form action="{{ route('admin.major.destroy', $major->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete this major?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Remove</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- New Majors -->
                        <div id="majors-container">
                            <!-- Dynamic major fields will be added here -->
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-major-btn">
                            + Add Major
                        </button>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <a href="{{ route('Dashboard.admin', ['tab' => 'departments']) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('majors-container');
            const addBtn = document.getElementById('add-major-btn');

            function addMajorField(value = '') {
                const div = document.createElement('div');
                div.className = 'major-item';
                div.innerHTML = `
                    <input type="text" class="form-control" name="new_majors[]" value="${value}" placeholder="Enter major name">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-major-btn">Remove</button>
                `;
                container.appendChild(div);

                div.querySelector('.remove-major-btn').addEventListener('click', function() {
                    div.remove();
                });
            }

            addBtn.addEventListener('click', function() {
                addMajorField();
            });

            // Restore old values if validation failed
            @if(old('new_majors'))
                @foreach(old('new_majors') as $major)
                    @if($major)
                        addMajorField('{{ $major }}');
                    @endif
                @endforeach
            @endif
        });
    </script>

</x-layouts.app>
