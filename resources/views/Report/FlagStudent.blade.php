<x-layouts.app>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href={{route('Dashboard.teacher')}}
                                class="text-decoration-none text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item active fw-bold" aria-current="page text-primary">Flag for Counseling
                        </li>
                    </ol>
                </nav>
                <h3 class="fw-bold mt-2">Counseling Referral</h3>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body text-center p-4">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                            style="width: 80px; height: 80px;">
                            <span class="material-symbols-rounded text-primary" style="font-size: 40px;">person</span>
                        </div>
                        <h5 class="fw-bold mb-1">{{ $student->full_name ?? "No Student Name" }}</h5>
                        <p class="text-muted small mb-3">Section: {{ $student->section ?? "No Section" }}</p>

                        <hr class="my-4 opacity-25">

                        <div class="text-start">
                            <label class="small text-muted d-block mb-1">Current Subject</label>
                            <div class="d-flex align-items-center">
                                <span class="material-symbols-rounded me-2 text-warning">menu_book</span>
                                <span class="fw-semibold">{{ Auth::user()->subject ?? "No Subject" }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">Evaluation Details</h5>
                        <p class="text-muted small">Please provide the reasons for this referral.</p>
                    </div>
                    <div class="card-body p-4">
                        <form action={{route('flag.submit')}} method="POST">
                            @csrf
                            @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 small">
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            <input type="hidden" name="student_id" value="{{ $student->id ?? " No Student Id" }}">
                            <input type="hidden" name="teacher_id" value="{{ Auth::id() }}">


                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Urgency Status (Auto)</label>
                                @php
                                $urg = $autoUrgency ?? 'mid';
                                $lowBtnClass = $urg === 'low' ? 'btn-success text-white' : 'btn-outline-success';
                                $midBtnClass = $urg === 'mid' ? 'btn-warning text-dark' : 'btn-outline-warning';
                                $highBtnClass = $urg === 'high' ? 'btn-danger text-white' : 'btn-outline-danger';

                                // Submit button color based on urgency
                                $submitClass = 'btn-primary';
                                if ($urg === 'high') $submitClass = 'btn-danger';
                                elseif ($urg === 'mid') $submitClass = 'btn-warning text-dark';
                                elseif ($urg === 'low') $submitClass = 'btn-success';
                                @endphp

                                <div class="d-flex gap-2 mt-1">
                                    <input type="radio" class="btn-check" name="urgency_display" id="low" value="low"
                                        autocomplete="off" {{ $urg=='low' ? 'checked' : '' }} disabled>
                                    <label class="btn {{ $lowBtnClass }} border-2 rounded-pill grow" for="low"
                                        data-urgency="low">Low</label>

                                    <input type="radio" class="btn-check" name="urgency_display" id="mid" value="mid"
                                        autocomplete="off" {{ $urg=='mid' ? 'checked' : '' }} disabled>
                                    <label class="btn {{ $midBtnClass }} border-2 rounded-pill grow" for="mid"
                                        data-urgency="mid">Mid</label>

                                    <input type="radio" class="btn-check" name="urgency_display" id="high" value="high"
                                        autocomplete="off" {{ $urg=='high' ? 'checked' : '' }} disabled>
                                    <label class="btn {{ $highBtnClass }} border-2 rounded-pill grow" for="high"
                                        data-urgency="high">High</label>
                                </div>
                                <small class="text-muted d-block mt-1">Urgency is determined automatically from the
                                    computed overall score.</small>
                                {{-- hidden input is only to submit the computed urgency (server computes as source of
                                truth) --}}
                                <input type="hidden" name="urgency" id="urgency_input" value="{{ $urg }}">
                                @if(isset($overall))
                                <small class="text-muted d-block mt-2">Computed overall score: <strong>{{
                                        number_format($overall, 2) }}%</strong></small>
                                @endif
                            </div>

                            <div class="col-12 mt-4">
                                <label class="form-label fw-semibold">Specific Factors / Observations</label>
                                <textarea name="comments" rows="4"
                                    class="form-control border-0 bg-light rounded-3 p-3 shadow-none"
                                    placeholder="Example: Student is constantly sleeping in class or using phone during quiz..."
                                    required></textarea>
                            </div>

                            <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                                <a href="/Teacher/Dashboard"
                                    class="btn btn-light rounded-pill px-4 fw-semibold text-muted">Cancel</a>
                                <button id="submitReferralBtn" type="submit"
                                    class="btn {{ $submitClass }} rounded-pill px-4 fw-semibold d-flex align-items-center">
                                    <span class="material-symbols-rounded me-2" style="font-size: 18px;">send</span>
                                    Submit Referral
                                </button>
                            </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            transition: transform 0.2s ease;
        }

        .form-select,
        .form-control {
            border: 1px solid #eee !important;
        }

        .form-select:focus,
        .form-control:focus {
            background-color: #fff !important;
            border-color: #0d6efd !important;
        }

        .btn-primary {
            background-color: #0d6efd;
            border: none;
        }

        .btn-check:checked+.btn-outline-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-check:checked+.btn-outline-warning {
            background-color: #ffc107;
            color: #000;
        }

        .btn-check:checked+.btn-outline-success {
            background-color: #198754;
            color: white;
        }

        /* Active urgency label styles when JS not present (server-side classes applied) */
        .btn-success.text-white {
            background-color: #198754 !important;
            border-color: #198754 !important;
        }

        .btn-warning.text-dark {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #000 !important;
        }

        .btn-danger.text-white {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
        }
    </style>

    <script>
        // Ensure visual highlight and submit button color match the computed urgency
        document.addEventListener('DOMContentLoaded', function () {
            const urg = document.getElementById('urgency_input')?.value || 'mid';
            // Add 'active' look to the matching label
            document.querySelectorAll('[data-urgency]').forEach(function (lbl) {
                if (lbl.dataset.urgency === urg) {
                    lbl.classList.remove('btn-outline-success', 'btn-outline-warning', 'btn-outline-danger');
                    if (urg === 'low') lbl.classList.add('btn-success', 'text-white');
                    if (urg === 'mid') lbl.classList.add('btn-warning', 'text-dark');
                    if (urg === 'high') lbl.classList.add('btn-danger', 'text-white');
                }
            });

            // adjust submit button style for clearer urgency signal
            const submit = document.getElementById('submitReferralBtn');
            if (submit) {
                submit.classList.remove('btn-primary', 'btn-success', 'btn-warning', 'btn-danger', 'text-dark');
                if (urg === 'low') submit.classList.add('btn-success');
                if (urg === 'mid') submit.classList.add('btn-warning', 'text-dark');
                if (urg === 'high') submit.classList.add('btn-danger');
            }
        });
    </script>
</x-layouts.app>