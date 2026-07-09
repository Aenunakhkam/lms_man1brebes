<table>
    <tr>
        <td colspan="8" style="font-size: 16px; font-weight: bold; text-align: center;">{{ $school_name }}</td>
    </tr>
    <tr>
        <td colspan="8" style="font-size: 10px; text-align: center;">{{ $school_address }} | Telp: {{ $school_phone }}</td>
    </tr>
    <tr>
        <td colspan="8"></td>
    </tr>
    <tr>
        <td colspan="8" style="font-size: 14px; font-weight: bold; text-align: center; text-decoration: underline;">REKAPITULASI HASIL UJIAN</td>
    </tr>
    <tr>
        <td colspan="3" style="font-weight: bold; text-align: left;">Mata Pelajaran: {{ $quiz->subject->name }}</td>
        <td colspan="2"></td>
        <td colspan="3" style="font-weight: bold; text-align: right;">Waktu: {{ $quiz->duration_minutes }} Menit</td>
    </tr>
    <tr>
        <td colspan="3" style="font-weight: bold; text-align: left;">Judul Ujian: {{ $quiz->title }}</td>
        <td colspan="2"></td>
        <td colspan="3" style="font-weight: bold; text-align: right;">Guru Pengampu: {{ $quiz->teacher->name }}</td>
    </tr>
    <tr>
        <td colspan="3" style="font-weight: bold; text-align: left;">Tahun Pelajaran: {{ $academic_year }}</td>
        <td colspan="2"></td>
        <td colspan="3" style="font-weight: bold; text-align: right;">Semester: {{ $semester }}</td>
    </tr>
    <tr>
        <td colspan="3" style="font-weight: bold; text-align: left;">Jumlah Peserta: {{ count($results) }} Siswa</td>
        <td colspan="5"></td>
    </tr>
    <tr>
        <td colspan="8"></td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000;">No</th>
            <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: left; border: 1px solid #000000;">Nama Siswa</th>
            <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000;">NIS</th>
            <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000;">Kelas</th>
            <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000;">Skor</th>
            <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000;">Benar</th>
            <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000;">Salah</th>
            <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($results as $index => $result)
        <tr>
            <td style="text-align: center; border: 1px solid #000000;">{{ $index + 1 }}</td>
            <td style="text-align: left; border: 1px solid #000000;">{{ $result->student->name }}</td>
            <td style="text-align: center; border: 1px solid #000000;">{{ $result->student->nis ?? '-' }}</td>
            <td style="text-align: center; border: 1px solid #000000;">{{ $result->student_class_name }}</td>
            <td style="text-align: center; border: 1px solid #000000;">{{ $result->score }}</td>
            <td style="text-align: center; border: 1px solid #000000;">{{ $result->total_points }}</td>
            <td style="text-align: center; border: 1px solid #000000;">{{ $quiz->questions_count - $result->total_points }}</td>
            <td style="text-align: center; border: 1px solid #000000;">{{ $result->status == 'completed' ? 'Selesai' : 'Sedang Mengerjakan' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
