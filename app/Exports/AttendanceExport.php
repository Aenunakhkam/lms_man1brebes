<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $classId;
    protected $month;

    public function __construct($classId = null, $month = null)
    {
        $this->classId = $classId;
        $this->month = $month;
    }

    public function collection()
    {
        $query = Attendance::with(['student', 'class']);
        
        if ($this->classId) {
            $query->where('class_id', $this->classId);
        }
        
        if ($this->month) {
            $query->whereMonth('date', date('m', strtotime($this->month)))
                  ->whereYear('date', date('Y', strtotime($this->month)));
        }

        return $query->orderBy('date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Siswa',
            'NISN',
            'Kelas',
            'Status Kehadiran',
        ];
    }

    public function map($attendance): array
    {
        return [
            date('d-m-Y', strtotime($attendance->date)),
            $attendance->student->name,
            $attendance->student->nisn ?? '-',
            $attendance->class->name,
            $attendance->status,
        ];
    }
}
