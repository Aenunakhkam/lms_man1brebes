<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassModel as Classes;
use App\Models\Subject;
use App\Models\User;
use App\Models\Grade;
use App\Models\Attendance;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use App\Exports\GradesExport;

class ReportController extends Controller
{
    private function getSettings() {
        return \App\Models\Setting::first() ?? new \App\Models\Setting([
            'school_name' => 'LMS MAN 1 Brebes',
            'headmaster_name' => 'H. Kepala Madrasah, S.Pd, M.Pd',
            'headmaster_nip' => '19700101 199512 1 001'
        ]);
    }

    public function exportGradesPdf(Request $request)
    {
        $query = Grade::with(['student', 'class', 'subject']);
        
        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }

        $grades = $query->get()->groupBy('student_id');
        $students = User::whereIn('id', $grades->keys())->get()->keyBy('id');
        
        $settings = $this->getSettings();
        $className = $request->class_id ? Classes::find($request->class_id)->name : 'Semua Kelas';
        $subjectName = $request->subject_id ? Subject::find($request->subject_id)->name : 'Semua Mata Pelajaran';

        $data = [
            'grades' => $grades,
            'students' => $students,
            'class' => (object)['name' => $className],
            'subject' => (object)['name' => $subjectName],
            'teacher' => (object)['name' => 'Admin (Rekapitulasi)', 'nip' => '-'],
            'categories' => ['Tugas', 'UH', 'UTS', 'UAS'], // Dummy categories for admin overview
            'school_name' => $settings->school_name ?? 'LMS MAN 1 Brebes',
            'location' => 'Brebes',
            'school_address' => $settings->school_address ?? 'Jl. Jenderal Sudirman No. 12',
            'school_phone' => $settings->school_phone ?? '(0283) 123456',
            'school_website' => $settings->school_website ?? 'www.man1brebes.sch.id',
            'isAdmin' => true
        ];

        $pdf = Pdf::loadView('exports.grades_pdf', $data)->setPaper('a4', 'landscape');
        
        $filename = 'Rekap_Nilai_' . Str::slug($className) . '_' . Str::slug($subjectName) . '.pdf';
        if (ob_get_length()) ob_end_clean();
        
        return $pdf->download($filename);
    }

    public function exportGradesExcel(Request $request)
    {
        $className = $request->class_id ? Classes::find($request->class_id)->name : 'Semua Kelas';
        $subjectName = $request->subject_id ? Subject::find($request->subject_id)->name : 'Semua Mata Pelajaran';
        
        $filename = 'Rekap_Nilai_' . Str::slug($className) . '_' . Str::slug($subjectName) . '.xlsx';
        
        return Excel::download(new GradesExport($request->class_id, $request->subject_id), $filename);
    }

    public function exportAttendancePdf(Request $request)
    {
        $query = Attendance::with(['student', 'class']);
        
        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->month) {
            $query->whereMonth('date', date('m', strtotime($request->month)))
                  ->whereYear('date', date('Y', strtotime($request->month)));
        }

        $attendances = $query->orderBy('date', 'desc')->get()->groupBy('student_id');
        $students = User::whereIn('id', $attendances->keys())->get()->keyBy('id');
        
        $settings = $this->getSettings();
        $className = $request->class_id ? Classes::find($request->class_id)->name : 'Semua Kelas';
        $period = $request->month ? date('F Y', strtotime($request->month)) : 'Semua Waktu';

        $data = [
            'attendances' => $attendances,
            'students' => $students,
            'class' => (object)['name' => $className],
            'period' => $period,
            'headmaster_name' => $settings->headmaster_name ?? 'H. Kepala Madrasah, S.Pd, M.Pd',
            'headmaster_nip' => $settings->headmaster_nip ?? '19700101 199512 1 001',
            'school_name' => $settings->school_name ?? 'LMS MAN 1 Brebes',
            'location' => 'Brebes',
            'school_address' => $settings->school_address ?? 'Jl. Jenderal Sudirman No. 12',
            'school_phone' => $settings->school_phone ?? '(0283) 123456',
            'school_website' => $settings->school_website ?? 'www.man1brebes.sch.id',
            'isAdmin' => true
        ];

        $pdf = Pdf::loadView('exports.attendance_pdf', $data)->setPaper('a4', 'landscape');
        
        $filename = 'Presensi_' . Str::slug($className) . '_' . Str::slug($period) . '.pdf';
        if (ob_get_length()) ob_end_clean();
        
        return $pdf->download($filename);
    }

    public function exportAttendanceExcel(Request $request)
    {
        $className = $request->class_id ? Classes::find($request->class_id)->name : 'Semua Kelas';
        $period = $request->month ? date('F Y', strtotime($request->month)) : 'Semua Waktu';
        
        $filename = 'Presensi_' . Str::slug($className) . '_' . Str::slug($period) . '.xlsx';
        
        return Excel::download(new \App\Exports\AttendanceExport($request->class_id, $request->month), $filename);
    }
}
