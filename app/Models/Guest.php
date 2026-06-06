<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    protected $fillable = [
        'name', 'stage_name', 'phone', 'method', 'contact', 'reminder_time', 'reminded_at',
    ];

    protected $casts = [
        'reminded_at' => 'datetime',
    ];
}
