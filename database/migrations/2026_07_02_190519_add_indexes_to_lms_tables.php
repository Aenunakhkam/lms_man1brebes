<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->index('class_id');
        });

        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->index('student_id');
            $table->index('assignment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex(['class_id']);
        });

        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
            $table->dropIndex(['assignment_id']);
        });
    }
};
