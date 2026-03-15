<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('class_quiz', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->timestamps();
        });

        // Migrate existing class data to the pivot table
        $quizzes = DB::table('quizzes')->whereNotNull('class_id')->get();
        foreach ($quizzes as $quiz) {
            DB::table('class_quiz')->insert([
                'class_id' => $quiz->class_id,
                'quiz_id' => $quiz->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Drop the old class_id column
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropColumn('class_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add class_id column to quizzes
        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('cascade');
        });

        // Migrate data back (taking the first class associated with the quiz)
        $quizzes = DB::table('quizzes')->get();
        foreach ($quizzes as $quiz) {
            $pivot = DB::table('class_quiz')->where('quiz_id', $quiz->id)->first();
            if ($pivot) {
                DB::table('quizzes')->where('id', $quiz->id)->update(['class_id' => $pivot->class_id]);
            }
        }

        Schema::dropIfExists('class_quiz');
    }
};
