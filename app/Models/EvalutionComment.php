<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvalutionComment extends Model
{
    //

    protected $table = 'evaluations';

    protected $fillable = [
        'student_id',
        'teacher_id',
        'status',
        'urgency',
        'comments',
        'category',
        'scheduled_at', // Siguraduhing nandito ito
    ];

    // Para ma-format natin ang date sa Blade nang maayos
    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    //  declare relationshio for  teacher and evalation teaher can flag many students

    // Relationship para sa Student
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // Relationship para sa Teacher (Instructor)
    public function teacher(): BelongsTo
    {
        // Ginamit ang 'teaher_id' dahil iyon ang nakasulat sa iyong migration file
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
