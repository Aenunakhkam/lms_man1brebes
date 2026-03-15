<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PublicStatsController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\DepartmentController;
use App\Http\Controllers\Api\Admin\SubjectController;
use App\Http\Controllers\Api\Admin\ClassController;
use App\Http\Controllers\Api\Admin\ScheduleController;
use App\Http\Controllers\Api\Admin\QuizController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\AnnouncementController;
use App\Http\Controllers\Api\ProfileController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::get('/public-stats', [PublicStatsController::class, 'index']);
Route::get('/settings', [SettingController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/announcements', [\App\Http\Controllers\Api\AnnouncementController::class, 'index']);

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);
    Route::delete('/profile/photo', [ProfileController::class, 'removePhoto']);
    Route::post('/profile/password', [ProfileController::class, 'changePassword']);

    // Admin Routes
    Route::group(['middleware' => ['role:admin'], 'prefix' => 'admin'], function () {
        Route::get('/dashboard-stats', [DashboardController::class, 'stats']);
        
        // Settings
        Route::get('/settings', [SettingController::class, 'index']);
        Route::post('/settings', [SettingController::class, 'update']);
        Route::delete('/settings/logo', [SettingController::class, 'removeLogo']);
        
        // CRUD Routes
        Route::get('users/export', [UserController::class, 'export']);
        Route::get('users/template', [UserController::class, 'template']);
        Route::post('users/import', [UserController::class, 'import']);
        Route::apiResource('users', UserController::class);
        Route::apiResource('departments', DepartmentController::class);
        Route::apiResource('subjects', SubjectController::class);
        Route::apiResource('classes', ClassController::class);
        Route::apiResource('schedules', ScheduleController::class);

        // Class Member Routes (Rombel)
        Route::get('classes/{class}/students', [ClassController::class, 'getStudents']);
        Route::post('classes/{class}/students', [ClassController::class, 'addStudent']);
        Route::delete('classes/{class}/students/{studentId}', [ClassController::class, 'removeStudent']);

        // Quiz Results & Attempt Management
        Route::get('quizzes/{quiz}/results', [QuizController::class, 'getResults']);
        Route::get('quizzes/{quiz}/export/pdf', [QuizController::class, 'exportPdf']);
        Route::get('quizzes/{quiz}/export/excel', [QuizController::class, 'exportExcel']);
        Route::delete('quizzes/attempts/{attempt}', [QuizController::class, 'deleteAttempt']);
        Route::post('quizzes/attempts/{attempt}/block', [QuizController::class, 'blockAttempt']);
        Route::post('quizzes/attempts/{attempt}/unblock', [QuizController::class, 'unblockAttempt']);
        Route::get('quizzes/attempts/{attempt}/history', [QuizController::class, 'getAttemptHistory']);

        // CBT Routes
        Route::apiResource('quizzes', QuizController::class);
        Route::get('quizzes/{quiz}/questions', [QuizController::class, 'getQuestions']);
        Route::post('quizzes/{quiz}/questions', [QuizController::class, 'storeQuestion']);
        Route::put('quizzes/{quiz}/questions/{question}', [QuizController::class, 'updateQuestion']);
        Route::delete('quizzes/{quiz}/questions/{question}', [QuizController::class, 'destroyQuestion']);

        // Announcements
        Route::apiResource('announcements', AnnouncementController::class);
    });

    // Guru routes
    Route::middleware('role:guru')->prefix('guru')->group(function () {
        Route::get('/dashboard-stats', [\App\Http\Controllers\Api\Guru\GuruController::class, 'getDashboardStats']);
        Route::get('/schedules', [\App\Http\Controllers\Api\Guru\GuruController::class, 'getSchedules']);
        Route::get('/classes', [\App\Http\Controllers\Api\Guru\GuruController::class, 'getClasses']);
        Route::get('/classes/{class}/students', [\App\Http\Controllers\Api\Guru\GuruController::class, 'getStudentsInClass']);
        Route::get('/subjects', [\App\Http\Controllers\Api\Guru\GuruController::class, 'getSubjects']);
        Route::get('/materials', [\App\Http\Controllers\Api\Guru\GuruController::class, 'getMaterials']);
        Route::post('/materials', [\App\Http\Controllers\Api\Guru\GuruController::class, 'storeMaterial']);
        Route::get('/assignments', [\App\Http\Controllers\Api\Guru\GuruController::class, 'getAssignments']);
        Route::post('/assignments', [\App\Http\Controllers\Api\Guru\GuruController::class, 'storeAssignment']);
        
        // Attendance
        Route::get('/attendance/students', [\App\Http\Controllers\Api\Guru\GuruController::class, 'getStudentsForAttendance']);
        Route::post('/attendance', [\App\Http\Controllers\Api\Guru\GuruController::class, 'storeAttendance']);

        // Grades
        Route::get('/grades', [\App\Http\Controllers\Api\Guru\GuruController::class, 'getGrades']);
        Route::post('/grades', [\App\Http\Controllers\Api\Guru\GuruController::class, 'storeGrades']);
        Route::delete('/grades', [\App\Http\Controllers\Api\Guru\GuruController::class, 'deleteGrades']);
        Route::get('/grades/summary', [\App\Http\Controllers\Api\Guru\GuruController::class, 'getGradeSummary']);
        Route::get('/grades/export/pdf', [\App\Http\Controllers\Api\Guru\GuruController::class, 'exportGradesPdf']);
        Route::get('/grades/export/excel', [\App\Http\Controllers\Api\Guru\GuruController::class, 'exportGradesExcel']);

        // Online Class Link
        Route::put('/schedules/{schedule}/online-link', [\App\Http\Controllers\Api\Guru\GuruController::class, 'updateOnlineLink']);
    });

    // Siswa routes
    Route::middleware('role:siswa')->prefix('siswa')->group(function () {
        Route::get('/dashboard-stats', [\App\Http\Controllers\Api\Siswa\SiswaController::class, 'getDashboardStats']);
        Route::get('/schedules', [\App\Http\Controllers\Api\Siswa\SiswaController::class, 'getSchedules']);
        Route::get('/materials', [\App\Http\Controllers\Api\Siswa\SiswaController::class, 'getMaterials']);
        Route::get('/assignments', [\App\Http\Controllers\Api\Siswa\SiswaController::class, 'getAssignments']);
        Route::get('/grades', [\App\Http\Controllers\Api\Siswa\SiswaController::class, 'getGrades']);
        Route::get('/attendance', [\App\Http\Controllers\Api\Siswa\SiswaController::class, 'getAttendance']);

        // CBT Routes
        Route::get('/cbt', [\App\Http\Controllers\Api\Siswa\QuizController::class, 'index']);
        Route::post('/cbt/{quiz}/start', [\App\Http\Controllers\Api\Siswa\QuizController::class, 'start']);
        Route::get('/cbt/attempts/{attempt}/questions', [\App\Http\Controllers\Api\Siswa\QuizController::class, 'getQuestions']);
        Route::post('/cbt/attempts/{attempt}/answer', [\App\Http\Controllers\Api\Siswa\QuizController::class, 'submitAnswer']);
        Route::post('/cbt/attempts/{attempt}/finish', [\App\Http\Controllers\Api\Siswa\QuizController::class, 'finish']);
    });
});
