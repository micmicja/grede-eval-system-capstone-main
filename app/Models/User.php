<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'username',
        'password',
        'role',
        'section',
        'subject',
    ];

    //  create relationship with attendance
    public function attendance()
    {
        return $this->hasMany(attendance::class);
    }

    // create relationship with quiz_exam_activity
    public function quiz_exam_activity()
    {
        return $this->hasMany(quiz_exam_activity::class);
    }

    // Relationship with teacher settings
    public function teacherSetting()
    {
        return $this->hasOne(\App\Models\TeacherSetting::class);
    }

    public function flagCreated(){

        return $this->hasMany(EvalutionComment::class,'teacher_id');
    }



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
