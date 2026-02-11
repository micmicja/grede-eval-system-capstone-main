{{-- Student Dropout Risk Assessment Modal --}}
<div class="modal fade" id="observationModal{{ $student->id }}" tabindex="-1" aria-labelledby="observationModalLabel{{ $student->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="observationModalLabel{{ $student->id }}">
                        Student Dropout Risk Assessment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('student.observation.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    
                    <div class="modal-body">
                        {{-- Risk Level Selection --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                General Average (0–100):
                            </label>
                            <div class="d-flex gap-2 flex-wrap">
                                <input type="radio" class="btn-check" name="risk_level" value="Low" id="riskLow{{ $student->id }}" autocomplete="off">
                                <label class="btn btn-outline-success" for="riskLow{{ $student->id }}">Low</label>

                                <input type="radio" class="btn-check" name="risk_level" value="Mid" id="riskMid{{ $student->id }}" autocomplete="off">
                                <label class="btn btn-outline-warning" for="riskMid{{ $student->id }}">Mid</label>

                                <input type="radio" class="btn-check" name="risk_level" value="Mid High" id="riskMidHigh{{ $student->id }}" autocomplete="off">
                                <label class="btn btn-outline-orange" for="riskMidHigh{{ $student->id }}" style="border-color: #fd7e14; color: #fd7e14;">Mid High</label>

                                <input type="radio" class="btn-check" name="risk_level" value="High" id="riskHigh{{ $student->id }}" autocomplete="off">
                                <label class="btn btn-outline-danger" for="riskHigh{{ $student->id }}">High</label>
                            </div>
                            <style>
                                /* Mid High button active state */
                                .btn-check:checked + .btn-outline-orange {
                                    background-color: #fd7e14 !important;
                                    border-color: #fd7e14 !important;
                                    color: white !important;
                                }
                            </style>
                            
                            
                            {{-- Grade Calculation Display --}}
                            <div class="alert alert-info mt-3" id="gradeCalculation{{ $student->id }}" style="display: none;">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Student Average:</span>
                                    <span class="fw-bold fs-5 text-primary" id="baseGrade{{ $student->id }}">0</span>
                                </div>
                            </div>
                            
                            <input type="hidden" name="general_average" id="generalAverageValue{{ $student->id }}">
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const modal{{ $student->id }} = document.getElementById('observationModal{{ $student->id }}');
                                let baseScore{{ $student->id }} = 0;
                                
                                // Function to calculate risk level based on score and behaviors
                                function calculateRiskLevel{{ $student->id }}() {
                                    // Count checked behaviors
                                    const behaviorCheckboxes = modal{{ $student->id }}.querySelectorAll('input[name="observed_behaviors[]"]');
                                    const checkedCount = Array.from(behaviorCheckboxes).filter(cb => cb.checked).length;
                                    
                                    // Each behavior adds 5% to risk (subtracts from score)
                                    const behaviorPenalty = checkedCount * 5;
                                    const adjustedScore = Math.max(0, baseScore{{ $student->id }} - behaviorPenalty);
                                    
                                    console.log('Base Score:', baseScore{{ $student->id }}, 'Behaviors:', checkedCount, 'Penalty:', behaviorPenalty, 'Adjusted:', adjustedScore);
                                    
                                    // Update display elements
                                    document.getElementById('baseGrade{{ $student->id }}').textContent = baseScore{{ $student->id }}.toFixed(2);
                                    document.getElementById('gradeCalculation{{ $student->id }}').style.display = 'block';
                                    
                                    // Store the adjusted score
                                    document.getElementById('generalAverageValue{{ $student->id }}').value = adjustedScore.toFixed(2);
                                    
                                    // Auto-select risk level based on adjusted score
                                    // New ranges: Low (90-100), Mid (76-89), Mid High (60-75), High (59 below)
                                    if (adjustedScore >= 90) {
                                        document.getElementById('riskLow{{ $student->id }}').checked = true;
                                        console.log('Selected: Low Risk');
                                    } else if (adjustedScore >= 76) {
                                        document.getElementById('riskMid{{ $student->id }}').checked = true;
                                        console.log('Selected: Mid Risk');
                                    } else if (adjustedScore >= 60) {
                                        document.getElementById('riskMidHigh{{ $student->id }}').checked = true;
                                        console.log('Selected: Mid High Risk');
                                    } else {
                                        document.getElementById('riskHigh{{ $student->id }}').checked = true;
                                        console.log('Selected: High Risk');
                                    }
                                }
                                
                                // Auto-calculate risk level when modal opens
                                modal{{ $student->id }}.addEventListener('shown.bs.modal', function() {
                                    // Fetch the student's overall score from the report page
                                    fetch('/Teacher/student/{{ $student->id }}/overall-score')
                                        .then(response => response.json())
                                        .then(data => {
                                            baseScore{{ $student->id }} = parseFloat(data.overall_score) || 0;
                                            console.log('Student {{ $student->full_name }} - Base Average:', baseScore{{ $student->id }});
                                            
                                            // Calculate initial risk level
                                            calculateRiskLevel{{ $student->id }}();
                                        })
                                        .catch(error => {
                                            console.error('Error fetching score:', error);
                                            // Default to High Risk if there's an error
                                            document.getElementById('riskHigh{{ $student->id }}').checked = true;
                                        });
                                });
                                
                                // Recalculate when behaviors are checked/unchecked
                                const behaviorCheckboxes = modal{{ $student->id }}.querySelectorAll('input[name="observed_behaviors[]"]');
                                behaviorCheckboxes.forEach(checkbox => {
                                    checkbox.addEventListener('change', function() {
                                        calculateRiskLevel{{ $student->id }}();
                                    });
                                });
                            });
                        </script>

                        {{-- Observed Student Behaviors --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Observed Student Behaviors:</label>
                            <div class="row g-2">
                                {{-- Left Column --}}
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="Lack of study habits" id="behavior1_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior1_{{ $student->id }}">
                                            Lack of study habits
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="No interest in school or subjects" id="behavior2_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior2_{{ $student->id }}">
                                            No interest in school or subjects
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="Lack of concentration" id="behavior3_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior3_{{ $student->id }}">
                                            Lack of concentration
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="Learning difficulties" id="behavior4_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior4_{{ $student->id }}">
                                            Learning difficulties
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="Poor reading comprehension" id="behavior5_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior5_{{ $student->id }}">
                                            Poor reading comprehension
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="Afraid to recite or ask questions" id="behavior6_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior6_{{ $student->id }}">
                                            Afraid to recite or ask questions
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="Incomplete or late submission of requirements" id="behavior7_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior7_{{ $student->id }}">
                                            Incomplete or late submission of requirements
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="Lack of parental support or supervision" id="behavior8_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior8_{{ $student->id }}">
                                            Lack of parental support or supervision
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="Family problems" id="behavior9_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior9_{{ $student->id }}">
                                            Family problems
                                        </label>
                                    </div>
                                </div>

                                {{-- Right Column --}}
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="Health issues" id="behavior10_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior10_{{ $student->id }}">
                                            Health issues
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="Lack of sleep" id="behavior11_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior11_{{ $student->id }}">
                                            Lack of sleep
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="Negative influence from friends" id="behavior12_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior12_{{ $student->id }}">
                                            Negative influence from friends
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="Overuse of gadgets / social media" id="behavior13_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior13_{{ $student->id }}">
                                            Overuse of gadgets / social media
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="Fear of failure or anxiety" id="behavior14_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior14_{{ $student->id }}">
                                            Fear of failure or anxiety
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="Lack of self-discipline" id="behavior15_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior15_{{ $student->id }}">
                                            Lack of self-discipline
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="Part-time work or job responsibilities" id="behavior16_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior16_{{ $student->id }}">
                                            Part-time work or job responsibilities
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="Poor relationship with professors or classmates" id="behavior17_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior17_{{ $student->id }}">
                                            Poor relationship with professors or classmates
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="observed_behaviors[]" value="Overreliance on online resources or AI" id="behavior18_{{ $student->id }}">
                                        <label class="form-check-label" for="behavior18_{{ $student->id }}">
                                            Overreliance on online resources or AI
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calculator"></i> Submit Assessment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
