<x-layouts.app title="Take Attendance">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Take Attendance</h2>
                <p class="text-muted">Mark attendance for your students</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('Dashboard.teacher') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
            </div>
        </div>

        {{-- Display success message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

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

        <div class="card shadow">
            <div class="card-body">
                <form method="POST" action="{{ route('attendance.store') }}">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="date" class="form-label fw-bold">Date</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ request('date', $date) }}" required>
                        </div>
                        <div class="col-md-8 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-info" id="markAllPresent">Mark All Present</button>
                            <button type="button" class="btn btn-sm btn-secondary ms-2" id="markAllAbsent">Mark All Absent</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 40%;">Student Name</th>
                                    <th style="width: 25%;">Section</th>
                                    <th style="width: 20%;">Subject</th>
                                    <th style="width: 10%;" class="text-center">Present</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $index => $student)
                                    @php
                                        $isPresent = isset($attendanceRecords[$student->full_name]) 
                                            ? $attendanceRecords[$student->full_name]->present 
                                            : false;
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $student->full_name }}</td>
                                        <td>{{ $student->section }}</td>
                                        <td>{{ $student->subject }}</td>
                                        <td class="text-center">
                                            <div class="form-check">
                                                <!-- Hidden input to always send 0 for unchecked -->
                                                <input type="hidden" name="attendance[{{ $student->full_name }}]" value="0">
                                                <input class="form-check-input attendance-checkbox" 
                                                       type="checkbox" 
                                                       id="attendance_{{ $student->id }}"
                                                       name="attendance[{{ $student->full_name }}]"
                                                       value="1"
                                                       {{ $isPresent ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No students found. <a href="{{ route('add-student') }}">Add a student</a> to get started.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('Dashboard.teacher') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Attendance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('markAllPresent').addEventListener('click', function() {
            document.querySelectorAll('.attendance-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
        });

        document.getElementById('markAllAbsent').addEventListener('click', function() {
            document.querySelectorAll('.attendance-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
        });
    </script>
</x-layouts.app>
