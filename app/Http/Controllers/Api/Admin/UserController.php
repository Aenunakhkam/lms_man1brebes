<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use Barryvdh\DomPDF\Facade\Pdf;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role');
        
        // Filter by role if provided
        if ($request->has('role')) {
            $role = $request->role;
            $query->whereHas('role', function($q) use ($role) {
                $q->where('name', $role);
            });
        }

        $users = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'nis' => 'nullable|string|unique:users',
            'nip' => 'nullable|string|unique:users',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $plainPassword = $validated['password'];
        $validated['password'] = Hash::make($validated['password']);
        $validated['password_raw'] = $plainPassword;
        
        $user = User::create($validated);
        $user->load('role');

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil ditambahkan',
            'data' => $user
        ], 201);
    }

    public function show(User $user)
    {
        $user->load('role');
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'nis' => 'nullable|string|unique:users,nis,' . $user->id,
            'nip' => 'nullable|string|unique:users,nip,' . $user->id,
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'is_active' => 'boolean'
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6';
        }

        $validated = $request->validate($rules);

        if ($request->filled('password')) {
            $plainPassword = $validated['password'];
            $validated['password'] = Hash::make($validated['password']);
            $validated['password_raw'] = $plainPassword;
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        $user->load('role');

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil diperbarui',
            'data' => $user
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus akun sendiri'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil dihapus'
        ]);
    }

    public function resetPassword(User $user)
    {
        $defaultPassword = 'password123';
        $user->update([
            'password'     => Hash::make($defaultPassword),
            'password_raw' => $defaultPassword,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset'
        ]);
    }

    public function export(Request $request)
    {
        $role = $request->input('role');
        $format = $request->input('format', 'excel');

        if ($format === 'pdf') {
            $query = User::with('role');
            if ($role) {
                $query->whereHas('role', function($q) use ($role) {
                    $q->where('name', $role);
                });
            }
            $users = $query->get();
            
            // Set options for better local compatibility
            $pdf = Pdf::loadView('admin.users.pdf', compact('users', 'role'))
                ->setPaper('a4', 'landscape')
                ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);
                
            return $pdf->download(($role ?: 'users') . "_export_" . date('Y-m-d') . ".pdf");
        }

        return Excel::download(new UsersExport($role), ($role ?: 'users') . "_export_" . date('Y-m-d') . ".xlsx");
    }

    public function template(Request $request)
    {
        $role = $request->role ?: 'siswa';
        $fileName = "template_import_{$role}.xlsx";
        $filePath = storage_path("app/{$fileName}");
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headings = ['nama', 'email', 'alamat', 'telepon'];
        if ($role === 'siswa') {
            $headings[] = 'nis';
        } elseif ($role === 'guru') {
            $headings[] = 'nip';
        }
        
        foreach ($headings as $colIndex => $heading) {
            $colLetter = chr(65 + $colIndex);
            $sheet->setCellValue($colLetter . '1', $heading);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
        
        $headerRange = 'A1:' . chr(64 + count($headings)) . '1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF203764'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }
        
        $writer->save($filePath);
        
        if (ob_get_length()) {
            ob_end_clean();
        }
        
        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'role' => 'required|string'
        ]);

        try {
            Excel::import(new UsersImport($request->role), $request->file('file'));
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diimpor'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor data: ' . $e->getMessage()
            ], 500);
        }
    }
}
