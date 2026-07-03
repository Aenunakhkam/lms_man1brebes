<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\QuizAttempt;
use App\Models\Setting;
use App\Exports\QuizResultsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Carbon\Carbon;

class QuizController extends Controller
{
    // --- Quiz CRUD ---

    public function index()
    {
        $query = Quiz::with(['classes', 'subject', 'teacher'])
            ->withCount('questions')
            ->withSum('questions as total_points', 'points')
            ->latest();

        // If the user is a guru, only show their own quizzes
        if (auth()->user()->role && auth()->user()->role->name === 'guru') {
            $query->where('teacher_id', auth()->id());
        }

        $quizzes = $query->get();
        return response()->json([
            'success' => true,
            'data' => $quizzes
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_active' => 'boolean',
            'max_attempts' => 'nullable|integer|min:1',
            'prevent_copy_paste' => 'boolean'
        ]);

        $validated['teacher_id'] = auth()->id();
        
        $quiz = Quiz::create([
            'teacher_id' => $validated['teacher_id'],
            'subject_id' => $validated['subject_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'is_active' => $validated['is_active'] ?? true,
            'max_attempts' => $validated['max_attempts'] ?? null,
            'prevent_copy_paste' => $validated['prevent_copy_paste'] ?? false,
        ]);

        $quiz->classes()->sync($validated['class_ids']);

        return response()->json([
            'success' => true,
            'message' => 'Ujian berhasil dibuat',
            'data' => $quiz->load(['classes', 'subject'])
                ->loadCount('questions')
                ->loadSum('questions as total_points', 'points')
        ], 201);
    }

    public function show(Quiz $quiz)
    {
        $quiz->load(['questions', 'classes', 'subject']);
        return response()->json([
            'success' => true,
            'data' => $quiz
        ]);
    }

    public function update(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_active' => 'boolean',
            'max_attempts' => 'nullable|integer|min:1',
            'prevent_copy_paste' => 'boolean'
        ]);

        $quiz->update([
            'subject_id' => $validated['subject_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'is_active' => $validated['is_active'] ?? true,
            'max_attempts' => $validated['max_attempts'] ?? null,
            'prevent_copy_paste' => $validated['prevent_copy_paste'] ?? false,
        ]);

        $quiz->classes()->sync($validated['class_ids']);

        return response()->json([
            'success' => true,
            'message' => 'Ujian berhasil diperbarui',
            'data' => $quiz->load(['classes', 'subject'])
                ->loadCount('questions')
                ->loadSum('questions as total_points', 'points')
        ]);
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return response()->json([
            'success' => true,
            'message' => 'Ujian berhasil dihapus'
        ]);
    }

    // --- Question CRUD ---

    public function getQuestions(Quiz $quiz)
    {
        return response()->json([
            'success' => true,
            'data' => $quiz->questions
        ]);
    }

    public function storeQuestion(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'option_a'      => 'required|string',
            'option_b'      => 'required|string',
            'option_c'      => 'nullable|string',
            'option_d'      => 'nullable|string',
            'option_e'      => 'nullable|string',
            'correct_answer' => 'required|in:a,b,c,d,e',
            'points'        => 'required|integer|min:0',
            'type'          => 'nullable|string|in:multiple_choice,essay,true_false',
            'question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('question_image')) {
            $path = $request->file('question_image')->store('questions', 'public');
            $validated['question_image'] = $path;
        }

        if (!isset($validated['type'])) {
            $validated['type'] = 'multiple_choice';
        }

        $question = $quiz->questions()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Soal berhasil ditambahkan',
            'data' => $question
        ], 201);
    }

    public function updateQuestion(Request $request, Quiz $quiz, QuizQuestion $question)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'option_a'      => 'required|string',
            'option_b'      => 'required|string',
            'option_c'      => 'nullable|string',
            'option_d'      => 'nullable|string',
            'option_e'      => 'nullable|string',
            'correct_answer' => 'required|in:a,b,c,d,e',
            'points'        => 'required|integer|min:0',
            'type'          => 'nullable|string|in:multiple_choice,essay,true_false',
            'question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('question_image')) {
            // Hapus gambar lama jika ada
            if ($question->question_image) {
                Storage::disk('public')->delete($question->question_image);
            }
            $path = $request->file('question_image')->store('questions', 'public');
            $validated['question_image'] = $path;
        }

        $question->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Soal berhasil diperbarui',
            'data' => $question
        ]);
    }

    public function destroyQuestion(Quiz $quiz, QuizQuestion $question)
    {
        if ($question->question_image) {
            Storage::disk('public')->delete($question->question_image);
        }
        $question->delete();
        return response()->json([
            'success' => true,
            'message' => 'Soal berhasil dihapus'
        ]);
    }

    // --- Quiz Results & Export ---

    public function getResults(Request $request, Quiz $quiz)
    {
        $search = $request->query('search');
        $classId = $request->query('class_id');

        $assignedClassIds = $quiz->classes->pluck('id');

        $query = \App\Models\User::whereHas('classes', function($q) use ($assignedClassIds) {
            $q->whereIn('classes.id', $assignedClassIds);
        });

        if ($classId) {
            $query->whereHas('classes', function($q) use ($classId) {
                $q->where('classes.id', $classId);
            });
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $students = $query->with(['classes'])->orderBy('name')->get();
        $attempts = QuizAttempt::where('quiz_id', $quiz->id)->get()->groupBy('student_id');

        $results = $students->map(function($student) use ($attempts) {
            $latestAttempt = $attempts->get($student->id)?->sortByDesc('created_at')->first();
            
            return [
                'id' => $latestAttempt?->id,
                'student' => $student,
                'student_class_name' => $student->classes->pluck('name')->join(', '),
                'status' => $latestAttempt?->status ?? 'not_started',
                'score' => $latestAttempt?->score ?? 0,
                'total_points' => $latestAttempt?->total_points ?? 0,
                'finished_at' => $latestAttempt?->finished_at,
            ];
        });

        // Set priority for sorting: in_progress, completed, blocked, not_started
        $results = $results->sort(function($a, $b) {
            $order = ['in_progress' => 0, 'completed' => 1, 'blocked' => 2, 'not_started' => 3];
            $valA = $order[$a['status']] ?? 99;
            $valB = $order[$b['status']] ?? 99;
            if ($valA === $valB) return strcmp($a['student']['name'], $b['student']['name']);
            return $valA <=> $valB;
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'quiz' => $quiz->load(['subject', 'teacher'])->loadCount('questions'),
                'results' => $results
            ]
        ]);
    }

    public function exportPdf(Request $request, Quiz $quiz)
    {
        $data = $this->prepareExportData($request, $quiz);
        $pdf = Pdf::loadView('exports.quiz_results', $data)->setPaper('a4', 'portrait');
        
        $filename = 'Hasil_Ujian_' . Str::slug($quiz->title) . '.pdf';
        
        if (ob_get_length()) ob_end_clean();
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request, Quiz $quiz)
    {
        $data = $this->prepareExportData($request, $quiz);
        $filename = 'Hasil_Ujian_' . Str::slug($quiz->title) . '.xlsx';
        
        if (ob_get_length()) ob_end_clean();
        return Excel::download(new QuizResultsExport($data), $filename);
    }

    private function prepareExportData(Request $request, Quiz $quiz)
    {
        $search = $request->query('search');
        $classId = $request->query('class_id');
        $assignedClassIds = $quiz->classes->pluck('id');

        $query = \App\Models\User::whereHas('classes', function($q) use ($assignedClassIds) {
            $q->whereIn('classes.id', $assignedClassIds);
        });

        if ($classId) {
            $query->whereHas('classes', function($q) use ($classId) {
                $q->where('classes.id', $classId);
            });
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $students = $query->with(['classes'])->orderBy('name')->get();
        $attempts = QuizAttempt::where('quiz_id', $quiz->id)->get()->groupBy('student_id');

        $results = $students->map(function($student) use ($attempts) {
            $latestAttempt = $attempts->get($student->id)?->sortByDesc('created_at')->first();
            return (object)[
                'student' => $student,
                'student_class_name' => $student->classes->pluck('name')->join(', '),
                'status' => $latestAttempt?->status ?? 'not_started',
                'score' => $latestAttempt?->score ?? 0,
                'total_points' => $latestAttempt?->total_points ?? 0,
                'finished_at' => $latestAttempt?->finished_at,
            ];
        });

        $settings = Setting::first();
        
        return [
            'quiz' => $quiz->load(['subject', 'teacher'])->loadCount('questions'),
            'results' => $results,
            'school_name' => $settings->school_name ?? 'LMS MAN 1 BREBES',
            'school_address' => $settings->school_address ?? 'Brebes, Jawa Tengah',
            'school_phone' => $settings->school_phone ?? '-',
        ];
    }

    public function deleteAttempt(QuizAttempt $attempt)
    {
        $attempt->delete();
        return response()->json([
            'success' => true,
            'message' => 'Hasil pengerjaan berhasil dihapus'
        ]);
    }

    public function blockAttempt(QuizAttempt $attempt)
    {
        $attempt->status = 'blocked';
        $attempt->save();
        return response()->json([
            'success' => true,
            'message' => 'Hasil pengerjaan berhasil diblokir'
        ]);
    }

    public function unblockAttempt(QuizAttempt $attempt)
    {
        $attempt->status = 'completed';
        $attempt->save();
        return response()->json([
            'success' => true,
            'message' => 'Blokir hasil pengerjaan berhasil dibuka'
        ]);
    }

    public function getAttemptHistory(QuizAttempt $attempt)
    {
        $quiz = $attempt->quiz->load('questions');
        $answers = \App\Models\QuizAnswer::where('quiz_attempt_id', $attempt->id)->get()->keyBy('quiz_question_id');

        $history = $quiz->questions->map(function($question) use ($answers) {
            $studentAnswer = $answers->get($question->id);
            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'options' => [
                    'a' => $question->option_a,
                    'b' => $question->option_b,
                    'c' => $question->option_c,
                    'd' => $question->option_d,
                    'e' => $question->option_e,
                ],
                'correct_answer' => $question->correct_answer,
                'student_answer' => $studentAnswer?->answer,
                'is_correct' => $studentAnswer?->is_correct ?? false,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'student' => $attempt->student,
                'quiz_title' => $quiz->title,
                'score' => $attempt->score,
                'history' => $history
            ]
        ]);
    }
}
