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
                        <label class="form-label">Course/Section</label>
                        <input type="text" class="form-control" name="section" required>
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

</x-layouts.app>