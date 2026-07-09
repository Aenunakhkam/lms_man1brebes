<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Export Data {{ ucfirst($role ?? 'Pengguna') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a2e; background: #fff; }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 3px solid #1a3a5c;
        }
        .header h2 {
            font-size: 16px;
            font-weight: bold;
            color: #1a3a5c;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header h3 { font-size: 12px; color: #2c6fb7; margin: 2px 0; }
        .header p  { font-size: 9px; color: #666; margin-top: 4px; }

        /* Badge role */
        .role-badge {
            display: inline-block;
            background: #2c6fb7;
            color: #fff;
            font-size: 9px;
            font-weight: bold;
            padding: 2px 10px;
            border-radius: 20px;
            margin-top: 4px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        thead tr th {
            background-color: #1a3a5c;
            color: #ffffff;
            font-weight: bold;
            padding: 7px 6px;
            text-align: center;
            font-size: 9px;
            border: 1px solid #0d2240;
            letter-spacing: 0.3px;
        }
        tbody tr td {
            padding: 6px 6px;
            font-size: 9px;
            border: 1px solid #d0d7e3;
            vertical-align: middle;
        }
        tbody tr:nth-child(even) td { background-color: #f0f4fa; }
        tbody tr:nth-child(odd)  td { background-color: #ffffff; }

        /* Password column highlight */
        .col-password {
            background-color: #fff9c4 !important;
            color: #7b4f00;
            font-weight: bold;
            text-align: center;
        }
        .no-col  { text-align: center; width: 28px; }
        .center  { text-align: center; }

        /* Status */
        .badge-aktif    { color: #1a7a30; font-weight: bold; }
        .badge-nonaktif { color: #c0392b; font-weight: bold; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: right;
            font-size: 8px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 4px;
        }

        /* Catatan keamanan */
        .security-note {
            margin-top: 12px;
            font-size: 8px;
            color: #c0392b;
            border: 1px dashed #e74c3c;
            padding: 4px 8px;
            background: #fff5f5;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Data {{ strtoupper($role ?? 'Pengguna') }}</h2>
        <h3>LMS MAN 1 BREBES</h3>
        <div class="role-badge">{{ strtoupper($role ?? 'Semua Role') }}</div>
        <p>Dicetak pada: {{ date('d F Y, H:i') }} WIB &nbsp;|&nbsp; Total: {{ count($users) }} pengguna</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="no-col">No</th>
                <th>Nama Lengkap</th>
                <th>Email</th>
                <th>Sandi / Password</th>
                <th>Role</th>
                <th>Status</th>
                <th>Alamat</th>
                <th>Telepon</th>
                @if($role === 'siswa') <th>NIS</th> @endif
                @if($role === 'guru')  <th>NIP</th> @endif
            </tr>
        </thead>
        <tbody>
            @foreach($users as $index => $user)
            <tr>
                <td class="no-col">{{ $index + 1 }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td class="col-password">{{ $user->password_raw ?? '(tidak tersimpan)' }}</td>
                <td class="center">{{ $user->role->display_name ?? $user->role->name ?? '-' }}</td>
                <td class="center {{ $user->is_active ? 'badge-aktif' : 'badge-nonaktif' }}">
                    {{ $user->is_active ? 'Aktif' : 'Non-Aktif' }}
                </td>
                <td>{{ $user->address ?? '-' }}</td>
                <td class="center">{{ $user->phone ?? '-' }}</td>
                @if($role === 'siswa') <td class="center">{{ $user->nis ?? '-' }}</td> @endif
                @if($role === 'guru')  <td class="center">{{ $user->nip ?? '-' }}</td> @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="security-note">
        ⚠️ Dokumen ini bersifat RAHASIA. Berisi informasi sandi akun pengguna. Harap dijaga kerahasiaannya dan tidak disebarluaskan kepada pihak yang tidak berwenang.
    </div>

    <div class="footer">
        Halaman {PAGE_NUM} dari {PAGE_COUNT} &nbsp;|&nbsp; Dicetak oleh Admin LMS MAN 1 BREBES
    </div>
</body>
</html>
