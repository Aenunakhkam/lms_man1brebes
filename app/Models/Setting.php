<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_name',
        'school_logo',
        'school_address',
        'school_phone',
        'school_email',
        'app_name',
        'app_developer',
        'app_version',
        'academic_year',
        'semester',
        'is_maintenance',
        'maintenance_message',
    ];

    protected $casts = [
        'is_maintenance' => 'boolean',
    ];
}
