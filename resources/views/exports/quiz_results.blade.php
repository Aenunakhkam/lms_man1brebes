<!DOCTYPE html>
<html>
<head>
    <title>Hasil Ujian - {{ $quiz->title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h2 { margin: 0; text-transform: uppercase; }
        .header p { margin: 5px 0; }
        .info { margin-bottom: 20px; }
        .info table { width: 100%; border: none; }
        .info td { padding: 3px 0; }
        .main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .main-table th, .main-table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .main-table th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $school_name }}</h2>
        <p>{{ $school_address }} | Telp: {{ $school_phone }}</p>
    </div>

    <div class="info">
        <h3 style="text-align: center; margin-bottom: 15px;">REKAPITULASI HASIL UJIAN</h3>
        <table>
            <tr>
                <td width="15%">Mata Pelajaran</td>
                <td width="2%">:</td>
                <td width="33%">{{ $quiz->subject->name }}</td>
                <td width="15%">Waktu</td>
                <td width="2%">:</td>
                <td width="33%">{{ $quiz->duration_minutes }} Menit</td>
            </tr>
            <tr>
                <td>Judul Ujian</td>
                <td>:</td>
                <td>{{ $quiz->title }}</td>
                <td>Guru Pengampu</td>
                <td>:</td>
                <td>{{ $quiz->teacher->name }}</td>
            </tr>
            <tr>
                <td>Tahun Pelajaran</td>
                <td>:</td>
                <td>{{ $academic_year }}</td>
                <td>Semester</td>
                <td>:</td>
                <td>{{ $semester }}</td>
            </tr>
            <tr>
                <td>Jumlah Peserta</td>
                <td>:</td>
                <td colspan="4">{{ count($results) }} Siswa</td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th width="5%" style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center;">No</th>
                <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: left;">Nama Siswa</th>
                <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center;">NIS</th>
                <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center;">Kelas</th>
                <th width="10%" style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center;">Skor</th>
                <th width="10%" style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center;">Benar</th>
                <th width="10%" style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center;">Salah</th>
                <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $index => $result)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="text-align: left;">{{ $result->student->name }}</td>
                <td style="text-align: center;">{{ $result->student->nis ?? '-' }}</td>
                <td style="text-align: center;">{{ $result->student_class_name }}</td>
                <td style="text-align: center;">{{ $result->score }}</td>
                <td style="text-align: center;">{{ $result->total_points }}</td>
                <td style="text-align: center;">{{ $quiz->questions_count - $result->total_points }}</td>
                <td style="text-align: center;">{{ $result->status == 'completed' ? 'Selesai' : 'Sedang Mengerjakan' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Brebes, {{ date('d F Y') }}</p>
        <br><br><br>
        <p><strong>{{ $quiz->teacher->name }}</strong></p>
    </div>
</body>
</html>
