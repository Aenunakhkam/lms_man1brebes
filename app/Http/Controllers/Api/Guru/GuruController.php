<?php

namespace App\Http\Controllers\Api\Guru;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Material;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\User;
use App\Models\Grade;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\GradesExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class GuruController extends Controller
{
    public function getDashboardStats()
    {
        $teacherId = Auth::id();
        
        // Count distinct classes assigned to this teacher via schedule
        $classesCount = Schedule::where('teacher_id', $teacherId)->distinct('class_id')->count('class_id');
        
        // Count total unique students in all classes assigned to this teacher
        $studentsCount = DB::table('student_class')
            ->whereIn('class_id', function($query) use ($teacherId) {
                $query->select('class_id')->from('schedules')->where('teacher_id', $teacherId);
            })->distinct('student_id')->count('student_id');
            
        $assignmentsCount = Assignment::where('teacher_id', $teacherId)->count();
        $materialsCount = Material::where('teacher_id', $teacherId)->count();

        // Get 5 most recently joined students in teacher's classes
        $recentStudents = User::whereHas('role', function($q) {
                $q->where('name', 'siswa');
            })
            ->whereHas('classes', function($q) use ($teacherId) {
                $q->whereIn('class_id', function($query) use ($teacherId) {
                    $query->select('class_id')->from('schedules')->where('teacher_id', $teacherId);
                });
            })
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'classes' => $classesCount,
                'students' => $studentsCount,
                'assignments' => $assignmentsCount,
                'materials' => $materialsCount,
                'recent_students' => $recentStudents
            ]
        ]);
    }

    public function getSchedules()
    {
        $teacherId = Auth::id();
        $schedules = Schedule::with(['class', 'subject'])
            ->where('teacher_id', $teacherId)
            ->orderByRaw("FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
    }

    public function getClasses()
    {
        $teacherId = Auth::id();
        // Get classes where this teacher has a schedule
        $classIds = Schedule::where('teacher_id', $teacherId)->pluck('class_id')->unique();
        $classes = ClassModel::with(['department'])
            ->withCount('students')
            ->whereIn('id', $classIds)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $classes
        ]);
    }

    public function getSubjects()
    {
        $teacherId = Auth::id();
        // Get subjects where this teacher has a schedule
        $subjectIds = Schedule::where('teacher_id', $teacherId)->pluck('subject_id')->unique();
        $subjects = Subject::whereIn('id', $subjectIds)->get();

        return response()->json([
            'success' => true,
            'data' => $subjects
        ]);
    }

    public function getMaterials()
    {
        $teacherId = Auth::id();
        $materials = Material::with(['class', 'subject'])
            ->where('teacher_id', $teacherId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $materials
        ]);
    }

    public function storeMaterial(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link' => 'nullable|string|url',
            'file' => 'nullable|file|max:10240', // 10MB
        ]);

        $data = $validated;
        $data['teacher_id'] = Auth::id();
        $data['type'] = 'file';

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('materials', 'public');
            $data['file_path'] = $path;
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
        }

        $material = Material::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Materi berhasil ditambahkan',
            'data' => $material
        ]);
    }

    public function updateMaterial(Request $request, Material $material)
    {
        if ($material->teacher_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link' => 'nullable|string|url',
            'file' => 'nullable|file|max:10240', // 10MB
        ]);

        $data = $validated;

        if ($request->hasFile('file')) {
            // Delete old file
            if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }

            $file = $request->file('file');
            $path = $file->store('materials', 'public');
            $data['file_path'] = $path;
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
        } elseif ($request->boolean('remove_file')) {
            // Remove existing file without uploading a new one
            if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }
            $data['file_path'] = null;
            $data['file_type'] = null;
            $data['file_size'] = null;
        }

        $material->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Materi berhasil diperbarui',
            'data' => $material
        ]);
    }

    public function destroyMaterial(Material $material)
    {
        if ($material->teacher_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return response()->json([
            'success' => true,
            'message' => 'Materi berhasil dihapus'
        ]);
    }

    public function getAssignments()
    {
        $teacherId = Auth::id();
        $assignments = Assignment::with(['class', 'subject'])
            ->withCount('submissions')
            ->where('teacher_id', $teacherId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $assignments
        ]);
    }

    public function storeAssignment(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_time' => 'required|date',
            'deadline' => 'required|date|after_or_equal:start_time',
            'max_score' => 'nullable|integer',
            'attachment' => 'nullable|file|max:10240'
        ]);

        $data = $validated;
        $data['teacher_id'] = Auth::id();

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('assignments/attachments', 'public');
            $data['attachment'] = $path;
        }

        $assignment = Assignment::create($data);

        // Kirim notifikasi ke siswa di kelas tersebut
        $class = ClassModel::with('students')->find($validated['class_id']);
        if ($class && $class->students->count() > 0) {
            $notificationsData = [];
            $now = Carbon::now();
            foreach ($class->students as $student) {
                $notificationsData[] = [
                    'user_id' => $student->id,
                    'title' => 'Tugas Baru',
                    'message' => 'Ada tugas baru: ' . $assignment->title,
                    'type' => 'assignment',
                    'link' => '/siswa/assignments/' . $assignment->id,
                    'is_read' => false,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
            if (count($notificationsData) > 0) {
                DB::table('notifications')->insert($notificationsData);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil ditambahkan',
            'data' => $assignment
        ]);
    }

    public function updateAssignment(Request $request, Assignment $assignment)
    {
        if ($assignment->teacher_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_time' => 'required|date',
            'deadline' => 'required|date|after_or_equal:start_time',
            'max_score' => 'nullable|integer',
            'attachment' => 'nullable|file|max:10240'
        ]);

        \Illuminate\Support\Facades\Log::info('updateAssignment payload: ', $request->all());
        \Illuminate\Support\Facades\Log::info('hasFile attachment: ' . ($request->hasFile('attachment') ? 'Yes' : 'No'));

        $data = $validated;

        if ($request->hasFile('attachment')) {
            // Hapus file lama jika ada
            if ($assignment->attachment && Storage::disk('public')->exists($assignment->attachment)) {
                Storage::disk('public')->delete($assignment->attachment);
            }
            
            $file = $request->file('attachment');
            $path = $file->store('assignments/attachments', 'public');
            $data['attachment'] = $path;
        } elseif ($request->boolean('remove_attachment')) {
            // Hapus file yang sudah ada jika pengguna memilih untuk menghapus
            if ($assignment->attachment && Storage::disk('public')->exists($assignment->attachment)) {
                Storage::disk('public')->delete($assignment->attachment);
            }
            $data['attachment'] = null;
        }

        $assignment->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil diperbarui',
            'data' => $assignment
        ]);
    }

    public function destroyAssignment(Assignment $assignment)
    {
        if ($assignment->teacher_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Hapus file attachment jika ada
        if ($assignment->attachment && Storage::disk('public')->exists($assignment->attachment)) {
            Storage::disk('public')->delete($assignment->attachment);
        }

        // Hapus nilai di tabel grades yang terhubung dengan tugas ini
        Grade::where('gradable_type', Assignment::class)
            ->where('gradable_id', $assignment->id)
            ->delete();

        // Hapus juga assignment submissions yang terhubung
        AssignmentSubmission::where('assignment_id', $assignment->id)->delete();

        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dihapus'
        ]);
    }

    public function getAssignmentSubmissions(Assignment $assignment)
    {
        if ($assignment->teacher_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $submissions = AssignmentSubmission::with('student')
            ->where('assignment_id', $assignment->id)
            ->get();

        // Include students in the class to show who hasn't submitted
        $students = $assignment->class->students;

        return response()->json([
            'success' => true,
            'data' => [
                'assignment' => $assignment,
                'submissions' => $submissions,
                'students' => $students
            ]
        ]);
    }

    public function gradeAssignmentSubmission(Request $request, $submissionId)
    {
        $submission = AssignmentSubmission::findOrFail($submissionId);
        $assignment = $submission->assignment;

        if ($assignment->teacher_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'score' => 'required|integer|min:0|max:' . ($assignment->max_score ?? 100),
            'feedback' => 'nullable|string'
        ]);

        $submission->update([
            'score' => $validated['score'],
            'feedback' => $validated['feedback'],
            'graded_at' => Carbon::now(),
            'status' => 'graded'
        ]);

        // Simpan/perbarui nilai di tabel grades agar muncul di menu Nilai Siswa
        Grade::updateOrCreate(
            [
                'student_id'    => $submission->student_id,
                'gradable_id'   => $assignment->id,
                'gradable_type' => Assignment::class,
            ],
            [
                'class_id'   => $assignment->class_id,
                'subject_id' => $assignment->subject_id,
                'grade_type' => 'assignment',
                'score'      => $validated['score'],
                'max_score'  => $assignment->max_score ?? 100,
                'notes'      => $validated['feedback'],
            ]
        );

        // Kirim notifikasi ke siswa bahwa tugasnya telah dinilai
        DB::table('notifications')->insert([
            'user_id'    => $submission->student_id,
            'title'      => 'Tugas Dinilai',
            'message'    => 'Tugas ' . $assignment->title . ' telah dinilai oleh Guru.',
            'type'       => 'assignment',
            'link'       => '/siswa/assignments/' . $assignment->id,
            'is_read'    => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Penilaian berhasil disimpan'
        ]);
    }

    public function getStudentsInClass(ClassModel $class)
    {
        $students = $class->students()->get();
        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }

    public function getStudentsForAttendance(Request $request)
    {
        $request->validate(['class_id' => 'required|exists:classes,id']);
        
        $students = User::whereHas('role', function($q) {
                $q->where('name', 'siswa');
            })
            ->whereHas('classes', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }

    public function getAttendance(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
        ]);

        $attendances = \App\Models\Attendance::where('class_id', $request->class_id)
            ->where('date', $request->date)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $attendances
        ]);
    }

    public function storeAttendance(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:users,id',
            'attendance.*.status' => 'required|in:Hadir,Izin,Sakit,Alpa',
        ]);

        foreach ($request->attendance as $att) {
            \App\Models\Attendance::updateOrCreate(
                [
                    'student_id' => $att['student_id'],
                    'class_id' => $request->class_id,
                    'date' => $request->date,
                ],
                [
                    'status' => $att['status'],
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil disimpan'
        ]);
    }

    public function deleteAttendance(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date'
        ]);

        \App\Models\Attendance::where('class_id', $request->class_id)
            ->where('date', $request->date)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data presensi berhasil dihapus'
        ]);
    }

    public function exportAttendancePdf(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $query = \App\Models\Attendance::with(['student', 'class']);
        $query->where('class_id', $request->class_id);
        
        if ($request->date) {
            $query->whereDate('date', $request->date);
        } elseif ($request->month) {
            $query->whereMonth('date', date('m', strtotime($request->month)))
                  ->whereYear('date', date('Y', strtotime($request->month)));
        }

        $attendances = $query->orderBy('date', 'desc')->get()->groupBy('student_id');
        $students = User::whereIn('id', $attendances->keys())->get()->keyBy('id');
        
        $settings = Setting::first();
        $className = ClassModel::find($request->class_id)->name;
        
        if ($request->date) {
            $period = date('d F Y', strtotime($request->date));
        } else {
            $period = $request->month ? date('F Y', strtotime($request->month)) : 'Semua Waktu';
        }
        $teacher = Auth::user();
        
        $schedule = Schedule::where('class_id', $request->class_id)
                                        ->where('teacher_id', $teacher->id)
                                        ->first();
        $subjectName = $schedule && $schedule->subject ? $schedule->subject->name : '-';

        $data = [
            'attendances' => $attendances,
            'students' => $students,
            'class' => (object)['name' => $className],
            'period' => $period,
            'exact_date' => $request->date ?? null,
            'subject_name' => $subjectName,
            'headmaster_name' => $settings->headmaster_name ?? 'H. Kepala Madrasah, S.Pd, M.Pd',
            'headmaster_nip' => $settings->headmaster_nip ?? '19700101 199512 1 001',
            'teacher' => (object)['name' => $teacher->name, 'nip' => $teacher->nip ?? '-'],
            'school_name' => $settings->school_name ?? 'LMS MAN 1 Brebes',
            'location' => 'Brebes',
            'school_address' => $settings->school_address ?? 'Jl. Jenderal Sudirman No. 12',
            'school_phone' => $settings->school_phone ?? '(0283) 123456',
            'school_website' => $settings->school_website ?? 'www.man1brebes.sch.id',
            'academic_year' => $settings->academic_year ?? (date('Y') . '/' . (date('Y') + 1)),
            'semester' => $settings->semester ?? '-',
            'isAdmin' => false
        ];

        $pdf = Pdf::loadView('exports.attendance_pdf', $data)->setPaper('a4', 'landscape');
        
        $filename = 'Presensi_' . Str::slug($className) . '_' . Str::slug($period) . '.pdf';
        if (ob_get_length()) ob_end_clean();
        
        return $pdf->download($filename);
    }

    public function exportAttendanceExcel(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $query = \App\Models\Attendance::with(['student', 'class']);
        $query->where('class_id', $request->class_id);
        
        if ($request->date) {
            $query->whereDate('date', $request->date);
        } elseif ($request->month) {
            $query->whereMonth('date', date('m', strtotime($request->month)))
                  ->whereYear('date', date('Y', strtotime($request->month)));
        }

        $attendances = $query->orderBy('date', 'desc')->get();
        $settings = Setting::first();
        $className = ClassModel::find($request->class_id)->name;
        
        if ($request->date) {
            $period = date('d F Y', strtotime($request->date));
        } else {
            $period = $request->month ? date('F Y', strtotime($request->month)) : 'Semua Waktu';
        }
        
        $teacher = Auth::user();
        
        $schedule = Schedule::where('class_id', $request->class_id)
                                        ->where('teacher_id', $teacher->id)
                                        ->first();
        $subjectName = $schedule && $schedule->subject ? $schedule->subject->name : '-';

        $data = [
            'attendances' => $attendances,
            'class' => (object)['name' => $className],
            'period' => $period,
            'subject_name' => $subjectName,
            'teacher' => (object)['name' => $teacher->name, 'nip' => $teacher->nip ?? '-'],
            'school_name' => $settings->school_name ?? 'LMS MAN 1 Brebes',
            'school_address' => $settings->school_address ?? 'Jl. Jenderal Sudirman No. 12',
            'school_phone' => $settings->school_phone ?? '(0283) 123456',
            'school_website' => $settings->school_website ?? 'www.man1brebes.sch.id',
            'academic_year' => $settings->academic_year ?? (date('Y') . '/' . (date('Y') + 1)),
            'semester' => $settings->semester ?? '-',
        ];

        $filename = 'Presensi_' . Str::slug($className) . '_' . Str::slug($period) . '.xlsx';
        if (ob_get_length()) ob_end_clean();

        return Excel::download(new \App\Exports\AttendanceExport($data), $filename);
    }

    public function getGrades(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'category' => 'required|string',
        ]);

        $grades = Grade::where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->where('grade_type', $request->category)
            ->whereNull('gradable_type') // hanya nilai manual
            ->get();

        return response()->json([
            'success' => true,
            'data' => $grades
        ]);
    }

    public function storeGrades(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'category' => 'required|string',
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:users,id',
            'grades.*.score' => 'required|numeric|min:0|max:100',
            'grades.*.note' => 'nullable|string',
        ]);

        foreach ($request->grades as $gradeData) {
            Grade::updateOrCreate(
                [
                    'student_id'    => $gradeData['student_id'],
                    'class_id'      => $request->class_id,
                    'subject_id'    => $request->subject_id,
                    'grade_type'    => $request->category,
                    'gradable_type' => null, // null = input manual guru (bukan dari tugas)
                    'gradable_id'   => null,
                ],
                [
                    'score'     => $gradeData['score'],
                    'notes'     => $gradeData['note'],
                    'max_score' => 100,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Nilai berhasil disimpan'
        ]);
    }

    public function deleteGrades(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'category' => 'required|string',
        ]);

        Grade::where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->where('grade_type', $request->category)
            ->whereNull('gradable_type') // hanya hapus nilai manual
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nilai kategori ' . $request->category . ' berhasil dihapus'
        ]);
    }

    public function updateGrade(Request $request, Grade $grade)
    {
        // Pastikan ini guru yang bersangkutan (validasi via kelas yang diampu)
        $teacherId = Auth::id();
        $isTeaching = Schedule::where('teacher_id', $teacherId)
            ->where('class_id', $grade->class_id)
            ->where('subject_id', $grade->subject_id)
            ->exists();

        if (!$isTeaching) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $grade->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Nilai berhasil diperbarui',
            'data' => $grade
        ]);
    }

    public function destroyGrade(Grade $grade)
    {
        $teacherId = Auth::id();
        $isTeaching = Schedule::where('teacher_id', $teacherId)
            ->where('class_id', $grade->class_id)
            ->where('subject_id', $grade->subject_id)
            ->exists();

        if (!$isTeaching) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Jika grade ini terhubung dengan tugas, reset status submission di siswa
        if ($grade->gradable_type === Assignment::class) {
            AssignmentSubmission::where('assignment_id', $grade->gradable_id)
                ->where('student_id', $grade->student_id)
                ->update([
                    'score' => null,
                    'status' => 'submitted',
                    'graded_at' => null
                ]);
        }

        $grade->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nilai siswa berhasil dihapus'
        ]);
    }

    public function getGradeSummary(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $students = User::whereHas('classes', function($q) use ($request) {
            $q->where('class_id', $request->class_id);
        })->get();

        $grades = Grade::with('gradable')
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->get();

        $categories = collect();
        foreach($grades as $grade) {
            if ($grade->gradable_type === Assignment::class && $grade->gradable) {
                $grade->grade_type = 'Tugas: ' . $grade->gradable->title;
            }
            $categories->push($grade->grade_type);
        }
        $categories = $categories->unique()->values();

        return response()->json([
            'success' => true,
            'data' => [
                'students' => $students,
                'categories' => $categories,
                'grades' => $grades
            ]
        ]);
    }

    public function exportGradesPdf(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $data = $this->prepareExportData($request);
        $pdf = Pdf::loadView('exports.grades_pdf', $data)->setPaper('a4', 'landscape');
        
        $className = Str::slug($data['class']->name);
        $subjectName = Str::slug($data['subject']->name);
        $filename = 'Rekap_Nilai_' . $className . '_' . $subjectName . '.pdf';
        
        // Clean filename from any potential newlines or suspicious characters
        $filename = preg_replace('/[\r\n\t]/', '', $filename);
        
        // Highly aggressive header cleaning to prevent "Header may not contain more than a single header"
        if (ob_get_length()) ob_end_clean();
        
        return $pdf->download($filename);
    }

    public function exportGradesExcel(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $data = $this->prepareExportData($request);
        $className = Str::slug($data['class']->name);
        $subjectName = Str::slug($data['subject']->name);
        $filename = 'Rekap_Nilai_' . $className . '_' . $subjectName . '.xlsx';
        
        $filename = preg_replace('/[\r\n\t]/', '', $filename);
        
        if (ob_get_length()) ob_end_clean();
        return Excel::download(new GradesExport($data), $filename);
    }

    private function prepareExportData(Request $request)
    {
        $class = ClassModel::findOrFail($request->class_id);
        $subject = Subject::findOrFail($request->subject_id);
        $teacher = Auth::user();

        $students = User::whereHas('classes', function($q) use ($request) {
            $q->where('class_id', $request->class_id);
        })->get();

        $grades = Grade::with('gradable')
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->get();

        $categories = collect();
        foreach($grades as $grade) {
            if ($grade->gradable_type === Assignment::class && $grade->gradable) {
                $grade->grade_type = 'Tugas: ' . $grade->gradable->title;
            }
            $categories->push($grade->grade_type);
        }
        $categories = $categories->unique()->values();

        $settings = Setting::first();

        return [
            'class' => $class,
            'subject' => $subject,
            'teacher' => $teacher,
            'students' => $students,
            'categories' => $categories,
            'grades' => $grades,
            'school_name' => $settings->school_name ?? 'MAN 1 BREBES',
            'location' => 'Brebes', // Fallback as city is not in table
            'school_address' => $settings->school_address ?? 'Jl. Jenderal Sudirman No. 12',
            'school_phone' => $settings->school_phone ?? '(0283) 123456',
            'school_website' => $settings->school_website ?? 'www.man1brebes.sch.id',
            'academic_year' => $settings->academic_year ?? (date('Y') . '/' . (date('Y') + 1)),
            'semester' => $settings->semester ?? '-',
        ];
    }

    public function updateOnlineLink(Request $request, Schedule $schedule)
    {
        // Ensure the schedule belongs to the authenticated guru
        if ($schedule->teacher_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke jadwal ini'
            ], 403);
        }

        $validated = $request->validate([
            'online_link' => 'nullable|url'
        ]);

        $schedule->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Link kelas online berhasil diperbarui',
            'data' => $schedule
        ]);
    }
}
