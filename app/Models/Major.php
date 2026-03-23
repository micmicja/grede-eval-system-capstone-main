<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    protected $fillable = ['department_id', 'name'];

    // Relationship with department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Relationship with users
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
