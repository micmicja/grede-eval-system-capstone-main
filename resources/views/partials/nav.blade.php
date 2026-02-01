{{-- create dashboard for admin  --}}
<x-layouts.app>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Admin Dashboard</h1>
        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Manage Users</h5>
                        <p class="card-text">Add, edit, or remove users from the system.</p>
                        <a href="#" class="btn btn-light">Go to Users</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">View Reports</h5>
                        <p class="card-text">Generate and view system reports.</p>
                        <a href="#" class="btn btn-light">Go to Reports</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">System Settings</h5>
                        <p class="card-text">Configure system preferences and settings.</p>
                        <a href="#" class="btn btn-light">Go to Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
</x-layouts.app>