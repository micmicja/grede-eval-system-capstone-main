<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSetting extends Model
{
   protected $fillable = [
    'user_id',
    'quiz_weight',
    'exam_weight',
    'activity_weight',
    'project_weight',
    'recitation_weight',
    'attendance_weight'
];
    public function user()
    {
         return $this->belongsTo(User::class);
    }
}
