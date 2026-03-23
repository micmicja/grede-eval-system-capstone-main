<x-layouts.app title="Edit Teacher">

    @push('head')
    <style>
        .form-wrapper { background: #fff; border-radius: 18px; padding: 2rem; border: 1px solid #e5e7eb; box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
        .page-title { font-weight:700; color:#1e293b; }
        .form-label { font-weight:600; }
    </style>
    @endpush

    <div class="container mt-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <a href="{{ route('Dashboard.admin') }}" class="btn btn-outline-secondary back-btn">← Back to Dashboard</a>
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
            <h3 class="page-title mb-4">Edit Teacher</h3>

            <form method="POST" action="{{ route('admin.teacher.update', $teacher->id) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="full_name" value="{{ old('full_name', $teacher->full_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select class="form-control @error('department_id') is-invalid @enderror" name="department_id" id="department_id" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $teacher->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->code }} - {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6" id="major_container" style="display: none;">
                        <label class="form-label">Major</label>
                        <select class="form-control @error('major_id') is-invalid @enderror" name="major_id" id="major_id">
                            <option value="">N/A</option>
                        </select>
                        @error('major_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Subject</label>
                        <input type="text" class="form-control" name="subject" value="{{ old('subject', $teacher->subject) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" value="{{ old('username', $teacher->username) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <a href="{{ route('Dashboard.admin') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const departmentSelect = document.getElementById('department_id');
            const majorContainer = document.getElementById('major_container');
            const majorSelect = document.getElementById('major_id');
            const currentMajorId = {{ $teacher->major_id ?? 'null' }};

            function loadMajors(departmentId, selectedMajorId = null) {
                if (!departmentId) {
                    majorContainer.style.display = 'none';
                    majorSelect.innerHTML = '<option value="">N/A</option>';
                    return;
                }

                // Fetch majors for selected department
                fetch(`/Admin/majors/${departmentId}`)
                    .then(response => response.json())
                    .then(majors => {
                        if (majors.length > 0) {
                            majorContainer.style.display = 'block';
                            majorSelect.innerHTML = '<option value="">Select Major</option>';
                            majors.forEach(major => {
                                const selected = selectedMajorId && major.id == selectedMajorId ? 'selected' : '';
                                majorSelect.innerHTML += `<option value="${major.id}" ${selected}>${major.name}</option>`;
                            });
                        } else {
                            majorContainer.style.display = 'none';
                            majorSelect.innerHTML = '<option value="">N/A</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching majors:', error);
                        majorContainer.style.display = 'none';
                        majorSelect.innerHTML = '<option value="">N/A</option>';
                    });
            }

            departmentSelect.addEventListener('change', function() {
                loadMajors(this.value);
            });

            // Load majors on page load if department is selected
            if (departmentSelect.value) {
                loadMajors(departmentSelect.value, currentMajorId);
            }
        });
    </script>

</x-layouts.app>