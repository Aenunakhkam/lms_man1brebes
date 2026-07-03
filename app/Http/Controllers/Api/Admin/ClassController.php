<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassModel as Classes;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        // Eager load department
        $classes = Classes::with(['department'])->get();
        return response()->json([
            'success' => true,
            'data' => $classes
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade_level' => 'required|string|in:10,11,12',
            'department_id' => 'required|exists:departments,id',
        ]);



        $class = Classes::create($validated);
        $class->load('department');

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil ditambahkan',
            'data' => $class
        ], 201);
    }

    public function show(Classes $class)
    {
        $class->load(['department']);
        // Append student count
        $class->student_count = $class->students()->count();
        
        return response()->json([
            'success' => true,
            'data' => $class
        ]);
    }

    public function update(Request $request, Classes $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade_level' => 'required|string|in:10,11,12',
            'department_id' => 'required|exists:departments,id',
        ]);

        $class->update($validated);
        $class->load('department');

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil diperbarui',
            'data' => $class
        ]);
    }

    // Student Management Methods
    public function getStudents(Classes $class)
    {
        $students = $class->students;
        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }

    public function addStudent(Request $request, Classes $class)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id'
        ]);

        // Check if student already in class
        if ($class->students()->where('student_id', $request->student_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa sudah ada di kelas ini'
            ], 422);
        }

        $class->students()->attach($request->student_id);

        return response()->json([
            'success' => true,
            'message' => 'Siswa berhasil ditambahkan ke kelas'
        ]);
    }

    public function removeStudent(Classes $class, $studentId)
    {
        $class->students()->detach($studentId);

        return response()->json([
            'success' => true,
            'message' => 'Siswa berhasil dihapus dari kelas'
        ]);
    }

    public function destroy(Classes $class)
    {
        $class->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil dihapus'
        ]);
    }
}
