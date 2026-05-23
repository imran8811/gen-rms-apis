<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';

    protected $fillable = ['name', 'role', 'phone', 'shift', 'salary', 'join_date', 'is_active', 'notes'];

    protected $casts = [
        'is_active' => 'boolean',
        'join_date' => 'date',
    ];
}
