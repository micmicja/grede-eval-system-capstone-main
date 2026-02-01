<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    //
    protected $table = 'students';
    protected $fillable = [
        'full_name',
        'section',
        'subject',
        'teacher_id',
    ];


    public function evaluations(): HasMany
    {
        // Dito natin kinokonekta ang Student sa kanyang mga records
        return $this->hasMany(EvalutionComment::class, 'student_id');
    }
}
