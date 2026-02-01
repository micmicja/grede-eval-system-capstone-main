<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    //
    protected $table = 'attendance';
    protected $fillable = [
        'full_name',
        'subject',
        'section',
        'user_id',
        'date',
        'present',
    ];

    //  create relationship with user
    public function user()
    {
        return $this->belongsTo(User::class);   
    }
}
