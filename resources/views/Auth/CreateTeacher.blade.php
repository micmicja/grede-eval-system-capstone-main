<x-layouts.app title="Create Teacher">

    @push('head')
    <style>
        /* Light modern touch (Bootstrap-only friendly) */
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

        .back-btn {
            border-radius: .65rem;
        }
    </style>
    @endpush

    <div class="container mt-5">

        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <a href="{{ route('Dashboard.admin') }}" class="btn btn-outline-secondary back-btn">
                ← Back to Dashboard
            </a>
        </div>

        {{--  display error --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Card Wrapper -->
        <div class="form-wrapper">

            <h3 class="page-title mb-4">Add New Teacher</h3>

            <form method="POST" action="{{ route('create-teacher.store') }}">
                @csrf

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="fullname" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select class="form-control @error('department_id') is-invalid @enderror" name="department_id" id="department_id" required>
                            <option value="">Select Department</option>
                            @foreach(\App\Models\Department::orderBy('code')->get() as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
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
                        <input type="text" class="form-control" name="subject" required>
                    </div>


                    <div>
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required>
                            @error('username')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                            <small class="form-text text-muted d-block mt-2">Password must be at least 8 characters long.</small>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>



                </div>

                <div class="mt-4 d-flex gap-2">
                    <a href="{{ route('Dashboard.admin') }}" class="btn btn-outline-secondary px-4">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        Create Teacher
                    </button>
                </div>

            </form>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const departmentSelect = document.getElementById('department_id');
            const majorContainer = document.getElementById('major_container');
            const majorSelect = document.getElementById('major_id');

            departmentSelect.addEventListener('change', function() {
                const departmentId = this.value;

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
                                majorSelect.innerHTML += `<option value="${major.id}">${major.name}</option>`;
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
            });

            // Trigger change event if department is pre-selected
            if (departmentSelect.value) {
                departmentSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>

</x-layouts.app>