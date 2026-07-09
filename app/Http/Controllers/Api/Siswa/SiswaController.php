<?php

namespace App\Http\Controllers\Api\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Material;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class SiswaController extends Controller
{
    public function getDashboardStats()
    {
        $studentId = Auth::id();
        $classId = DB::table('student_class')->where('student_id', $studentId)->value('class_id');

        if (!$classId) {
            return response()->json([
                'success' => true,
                'data' => [
                    'subjects_count' => 0,
                    'pending_assignments' => 0,
                    'completed_assignments' => 0,
                    'average_grade' => 0,
                    'upcoming_assignments' => [],
                    'recent_grades' => []
                ]
            ]);
        }

        // 1. Total Subjects
        $subjectsCount = Schedule::where('class_id', $classId)
            ->distinct('subject_id')
            ->count('subject_id');

        // 2. Assignments Stats
        $allAssignments = Assignment::where('class_id', $classId)->pluck('id');
        $submittedCount = AssignmentSubmission::where('student_id', $studentId)
            ->whereIn('assignment_id', $allAssignments)
            ->count();
        $pendingCount = count($allAssignments) - $submittedCount;

        // 3. Average Grade
        $averageGrade = Grade::where('student_id', $studentId)
            ->avg('score') ?: 0;

        // 4. Upcoming Assignments (Top 4)
        $upcomingAssignments = Assignment::with(['subject'])
            ->withExists(['submissions as is_submitted' => function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            }])
            ->where('class_id', $classId)
            ->where('deadline', '>=', Carbon::now())
            ->orderBy('deadline', 'asc')
            ->limit(4)
            ->get()
            ->map(function($assignment) {
                // withExists casts to boolean but let's just make sure
                $assignment->is_submitted = (bool) $assignment->is_submitted;
                
                // Calculate days remaining
                $now = Carbon::now();
                $due = Carbon::parse($assignment->deadline);
                $assignment->days_remaining = $now->diffInDays($due, false);
                
                return $assignment;
            });

        // 5. Recent Grades (Top 5)
        $recentGrades = Grade::with(['subject'])
            ->where('student_id', $studentId)
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'subjects_count' => $subjectsCount,
                'pending_assignments' => $pendingCount,
                'completed_assignments' => $submittedCount,
                'average_grade' => round($averageGrade, 1),
                'upcoming_assignments' => $upcomingAssignments,
                'recent_grades' => $recentGrades
            ]
        ]);
    }

    public function getSchedules()
    {
        $studentId = Auth::id();
        // Get class_id of this student
        $classId = DB::table('student_class')->where('student_id', $studentId)->value('class_id');

        if (!$classId) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $schedules = Schedule::with(['subject', 'teacher'])
            ->where('class_id', $classId)
            ->orderByRaw("FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
    }

    public function getMaterials()
    {
        $studentId = Auth::id();
        $classId = DB::table('student_class')->where('student_id', $studentId)->value('class_id');

        if (!$classId) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $materials = Material::with(['subject', 'teacher'])
            ->where('class_id', $classId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $materials
        ]);
    }

    public function getAssignments()
    {
        $studentId = Auth::id();
        $classId = DB::table('student_class')->where('student_id', $studentId)->value('class_id');

        if (!$classId) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $assignments = Assignment::with(['subject', 'teacher'])
            ->where('class_id', $classId)
            ->latest()
            ->get()
            ->map(function ($assignment) use ($studentId) {
                $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
                    ->where('student_id', $studentId)
                    ->first();
                $assignment->is_submitted = (bool)$submission;
                $assignment->submission = $submission;
                return $assignment;
            });

        return response()->json([
            'success' => true,
            'data' => $assignments
        ]);
    }

    public function getAssignment(Assignment $assignment)
    {
        $studentId = Auth::id();
        $classId = DB::table('student_class')->where('student_id', $studentId)->value('class_id');

        if ($assignment->class_id !== $classId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $assignment->load(['subject', 'teacher']);
        
        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $studentId)
            ->first();
            
        $assignment->is_submitted = (bool)$submission;
        $assignment->submission = $submission;

        return response()->json([
            'success' => true,
            'data' => $assignment
        ]);
    }

    public function submitAssignment(Request $request, Assignment $assignment)
    {
        $studentId = Auth::id();

        // Check if already submitted
        $existing = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $studentId)
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Anda sudah mengumpulkan tugas ini'], 400);
        }

        // Validate time
        $now = Carbon::now();
        if ($assignment->start_time && $now->lt(Carbon::parse($assignment->start_time))) {
            return response()->json(['success' => false, 'message' => 'Tugas belum dibuka'], 400);
        }

        if ($assignment->deadline && $now->gt(Carbon::parse($assignment->deadline))) {
            return response()->json(['success' => false, 'message' => 'Tugas sudah ditutup (melewati batas waktu)'], 400);
        }

        $validated = $request->validate([
            'content' => 'nullable|string',
            'file' => 'nullable|file|max:10240' // max 10MB
        ]);

        if (!$request->has('content') && !$request->hasFile('file')) {
            return response()->json(['success' => false, 'message' => 'Harap isi teks jawaban atau unggah file'], 422);
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('assignments/submissions', 'public');
        }

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $studentId,
            'content' => $validated['content'] ?? null,
            'file_path' => $filePath,
            'submitted_at' => $now,
            'status' => 'submitted'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dikumpulkan',
            'data' => $submission
        ]);
    }

    public function cancelSubmission(Assignment $assignment)
    {
        $studentId = Auth::id();

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $studentId)
            ->first();

        if (!$submission) {
            return response()->json(['success' => false, 'message' => 'Pengumpulan tidak ditemukan'], 404);
        }

        // Tidak bisa dibatalkan jika sudah dinilai oleh guru
        if ($submission->status === 'graded') {
            return response()->json([
                'success' => false,
                'message' => 'Pengumpulan tidak dapat dibatalkan karena sudah dinilai oleh guru'
            ], 403);
        }

        // Hapus file jika ada
        if ($submission->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($submission->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($submission->file_path);
        }

        $submission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengumpulan berhasil dibatalkan. Anda dapat mengumpulkan kembali.'
        ]);
    }

    public function getGrades()
    {
        $studentId = Auth::id();
        $grades = Grade::with(['subject', 'gradable'])
            ->where('student_id', $studentId)
            ->latest()
            ->get()
            ->map(function ($grade) {
                // Tambahkan field title agar mudah ditampilkan di frontend
                $grade->title = optional($grade->gradable)->title
                    ?? optional($grade->gradable)->name
                    ?? $grade->grade_type;
                return $grade;
            });

        return response()->json([
            'success' => true,
            'data' => $grades
        ]);
    }

    public function getAttendance()
    {
        $studentId = Auth::id();
        $attendances = \App\Models\Attendance::with('class')
            ->where('student_id', $studentId)
            ->orderBy('date', 'desc')
            ->get();

        $summary = [
            'hadir' => $attendances->where('status', 'Hadir')->count(),
            'izin' => $attendances->where('status', 'Izin')->count(),
            'sakit' => $attendances->where('status', 'Sakit')->count(),
            'alpa' => $attendances->where('status', 'Alpa')->count(),
            'total' => $attendances->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $attendances,
            'summary' => $summary
        ]);
    }
}
