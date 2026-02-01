<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentObservation extends Model
{
    protected $table = 'student_observations';
    
    protected $fillable = [
        'student_id',
        'teacher_id',
        'calculated_average',
        'risk_status',
        'observed_behaviors',
        'referred_to_councilor',
        'scheduled_at',
        'counseling_status',
    ];

    protected $casts = [
        'observed_behaviors' => 'array',
        'referred_to_councilor' => 'boolean',
        'calculated_average' => 'decimal:2',
        'scheduled_at' => 'datetime',
    ];

    /**
     * Get the student that owns the observation.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the teacher who created the observation.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
