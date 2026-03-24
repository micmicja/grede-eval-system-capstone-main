<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['code', 'name'];

    // Relationship with majors
    public function majors()
    {
        return $this->hasMany(Major::class);
    }

    // Relationship with users
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
