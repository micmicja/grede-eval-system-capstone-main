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
                        <label class="form-label">Section</label>
                        <input type="text" class="form-control" name="section" value="{{ old('section', $teacher->section) }}">
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

</x-layouts.app>