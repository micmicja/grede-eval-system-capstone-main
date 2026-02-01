<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz_exam_activity extends Model
{
    //
    protected $table = 'quiz_exam_activity';
    protected $fillable = [
        'full_name',
        'subject',
        'section',
        'user_id',
        'activity_type',
        'activity_title',
        'date_taken',
        'score',
        'weighted_score'
    ];

    //  create relationshiip wth user abou t quiz_exam_activity
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
