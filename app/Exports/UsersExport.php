<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class UsersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $role;

    public function __construct($role = null)
    {
        $this->role = $role;
    }

    public function title(): string
    {
        return match($this->role) {
            'siswa' => 'Data Siswa',
            'guru'  => 'Data Guru',
            'admin' => 'Data Admin',
            default => 'Data Pengguna',
        };
    }

    public function collection()
    {
        $query = User::with('role');
        if ($this->role) {
            $query->whereHas('role', function($q) {
                $q->where('name', $this->role);
            });
        }
        return $query->latest()->get();
    }

    public function headings(): array
    {
        $headings = ['No.', 'Nama Lengkap', 'Email', 'Sandi / Password', 'Role', 'Status', 'Alamat', 'No. Telepon'];
        if ($this->role === 'siswa') $headings[] = 'NIS';
        if ($this->role === 'guru')  $headings[] = 'NIP';
        return $headings;
    }

    public function map($user): array
    {
        static $index = 0;
        $index++;

        $map = [
            $index,
            $user->name,
            $user->email,
            $user->password_raw ?? '(tidak tersimpan)',
            $user->role->display_name ?? $user->role->name ?? '-',
            $user->is_active ? 'Aktif' : 'Non-Aktif',
            $user->address ?? '-',
            $user->phone   ?? '-',
        ];
        if ($this->role === 'siswa') $map[] = $user->nis ?? '-';
        if ($this->role === 'guru')  $map[] = $user->nip ?? '-';
        return $map;
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = $this->role === 'siswa' || $this->role === 'guru' ? 'I' : 'H';

        // Header row style
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1a3a5c'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);

        // All borders
        $sheet->getStyle("A1:{$lastCol}1")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setARGB('FFB0B0B0');

        // Password column — highlight kuning muda agar mudah dikenali
        $sheet->getStyle('D2:D' . ($sheet->getHighestRow()))->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFFF9C4'],
            ],
            'font' => ['bold' => true, 'color' => ['argb' => 'FF7B4F00']],
        ]);

        // Row height header
        $sheet->getRowDimension(1)->setRowHeight(30);

        return [];
    }
}
