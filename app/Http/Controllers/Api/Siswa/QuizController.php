<?php

namespace App\Http\Controllers\Api\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function index()
    {
        $student = auth()->user();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Get student's class IDs (handling multiple classes if applicable)
        $classIds = $student->classes->pluck('id');
        
        // Fetch quizzes that are active for the student's class
        $quizzes = Quiz::where('is_active', true)
            ->whereHas('classes', function($query) use ($classIds) {
                $query->whereIn('classes.id', $classIds);
            })
            ->with(['classes', 'subject'])
            ->get()
            ->map(function($quiz) use ($student) {
                $attempt = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('student_id', $student->id)
                    ->first();
                
                $quiz->my_attempt = $attempt;
                $quiz->status = $this->getQuizStatus($quiz, $attempt);
                return $quiz;
            });

        return response()->json([
            'success' => true,
            'data' => $quizzes
        ]);
    }

    private function getQuizStatus($quiz, $attempt)
    {
        $now = Carbon::now();
        
        if ($attempt && $attempt->status === 'completed') {
            return 'finished';
        }
        
        if ($attempt && $attempt->status === 'in_progress') {
            return 'in_progress';
        }
        
        if ($now->between($quiz->start_time, $quiz->end_time)) {
            return 'available';
        }
        
        if ($now->lt($quiz->start_time)) {
            return 'upcoming';
        }
        
        return 'expired';
    }

    public function start(Quiz $quiz)
    {
        $studentId = Auth::id();
        $now = Carbon::now();

        if (!$quiz->is_active || $now->lt($quiz->start_time) || $now->gt($quiz->end_time)) {
            return response()->json([
                'success' => false,
                'message' => 'Ujian tidak tersedia saat ini'
            ], 403);
        }

        $existingAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $studentId)
            ->first();

        if ($existingAttempt && $existingAttempt->status === 'completed') {
            // Check max attempts
            if ($quiz->max_attempts !== null) {
                $completedCount = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('student_id', $studentId)
                    ->where('status', 'completed')
                    ->count();
                
                if ($completedCount >= $quiz->max_attempts) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah mencapai batas maksimal pengerjaan (' . $quiz->max_attempts . ' kali)'
                    ], 403);
                }
            }
        }

        if ($existingAttempt && $existingAttempt->status === 'in_progress') {
            return response()->json([
                'success' => true,
                'data' => $existingAttempt
            ]);
        }

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $studentId,
            'attempt_number' => ($existingAttempt ? $existingAttempt->attempt_number + 1 : 1),
            'started_at' => $now,
            'status' => 'in_progress'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ujian dimulai',
            'data' => $attempt
        ]);
    }

    public function getQuestions(QuizAttempt $attempt)
    {
        if ($attempt->student_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($attempt->status !== 'in_progress') {
            return response()->json(['success' => false, 'message' => 'Ujian sudah selesai'], 400);
        }

        // Return questions without correct answers
        $questions = QuizQuestion::where('quiz_id', $attempt->quiz_id)
            ->get()
            ->map(function($q) use ($attempt) {
                $answer = QuizAnswer::where('quiz_attempt_id', $attempt->id)
                    ->where('quiz_question_id', $q->id)
                    ->first();
                
                return [
                    'id' => $q->id,
                    'question_text' => $q->question_text,
                    'option_a' => $q->option_a,
                    'option_b' => $q->option_b,
                    'option_c' => $q->option_c,
                    'option_d' => $q->option_d,
                    'my_answer' => $answer ? $answer->answer : null
                ];
            });

        $quiz = $attempt->quiz;
        $timeLeft = $quiz->duration_minutes * 60 - Carbon::now()->diffInSeconds($attempt->started_at);

        return response()->json([
            'success' => true,
            'data' => [
                'questions' => $questions,
                'time_left_seconds' => max(0, $timeLeft),
                'quiz_title' => $quiz->title,
                'quiz_settings' => [
                    'prevent_copy_paste' => (bool)$quiz->prevent_copy_paste
                ]
            ]
        ]);
    }

    public function submitAnswer(Request $request, QuizAttempt $attempt)
    {
        if ($attempt->student_id !== Auth::id() || $attempt->status !== 'in_progress') {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'question_id' => 'required|exists:quiz_questions,id',
            'answer' => 'required|in:a,b,c,d'
        ]);

        QuizAnswer::updateOrCreate(
            ['quiz_attempt_id' => $attempt->id, 'quiz_question_id' => $validated['question_id']],
            ['answer' => $validated['answer']]
        );

        return response()->json(['success' => true]);
    }

    public function finish(QuizAttempt $attempt)
    {
        if ($attempt->student_id !== Auth::id() || $attempt->status !== 'in_progress') {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $correctCount = 0;
        $questions = QuizQuestion::where('quiz_id', $attempt->quiz_id)->get();
        $totalQuestions = $questions->count();

        foreach ($questions as $q) {
            $answer = QuizAnswer::where('quiz_attempt_id', $attempt->id)
                ->where('quiz_question_id', $q->id)
                ->first();
            
            if ($answer) {
                $isCorrect = $answer->answer === $q->correct_answer;
                if ($isCorrect) $correctCount++;
                
                $answer->update([
                    'is_correct' => $isCorrect,
                    'points_earned' => $isCorrect ? 1 : 0
                ]);
            }
        }

        $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;

        $attempt->update([
            'finished_at' => Carbon::now(),
            'score' => $score,
            'total_points' => $correctCount,
            'status' => 'completed'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ujian selesai',
            'data' => [
                'score' => $score,
                'correct' => $correctCount,
                'total' => $totalQuestions
            ]
        ]);
    }
}
