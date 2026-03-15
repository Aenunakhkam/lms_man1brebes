<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Department; // Added based on department() relationship
use App\Models\User;       // Added based on students() relationship

class ClassModel extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'academic_year_id',
        'department_id',
        'name',
        'grade_level',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_class', 'class_id', 'student_id')
                    ->withTimestamps();
    }
    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'class_quiz', 'class_id', 'quiz_id')
                    ->withTimestamps();
    }
}
