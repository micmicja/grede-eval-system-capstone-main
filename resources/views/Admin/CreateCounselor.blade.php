<x-layouts.app title="Create Counselor">

    <style>
        .form-card {
            max-width: 600px;
            margin: 50px auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
        }
    </style>

    <div class="container">
        <div class="card form-card">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Create New Counselor</h4>
            </div>

            <div class="card-body p-4">
                {{-- Error messages --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.counselor.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="full_name" class="form-label fw-bold">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                            value="{{ old('full_name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label fw-bold">Username</label>
                        <input type="text" class="form-control" id="username" name="username" 
                            value="{{ old('username') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-bold">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('Dashboard.admin', ['tab' => 'counselors']) }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success">Create Counselor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-layouts.app>
