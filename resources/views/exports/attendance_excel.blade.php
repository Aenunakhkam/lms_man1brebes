<table>
    <tr>
        <td colspan="5" style="font-size: 16px; font-weight: bold; text-align: center;">{{ $school_name }}</td>
    </tr>
    <tr>
        <td colspan="5" style="font-size: 10px; text-align: center;">{{ $school_address }} | Telp: {{ $school_phone }}</td>
    </tr>
    <tr>
        <td colspan="5" style="font-size: 10px; text-align: center;">Website: {{ $school_website }}</td>
    </tr>
    <tr>
        <td colspan="5" style="font-size: 14px; font-weight: bold; text-align: center; text-decoration: underline;">REKAPITULASI KEHADIRAN SISWA</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight: bold; text-align: left;">KELAS: {{ strtoupper($class->name) }}</td>
        <td></td>
        <td colspan="2" style="font-weight: bold; text-align: right;">TAHUN PELAJARAN: {{ strtoupper($academic_year) }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight: bold; text-align: left;">PERIODE: {{ strtoupper($period) }}</td>
        <td></td>
        <td colspan="2" style="font-weight: bold; text-align: right;">SEMESTER: {{ strtoupper($semester) }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight: bold; text-align: left;">MATA PELAJARAN: {{ strtoupper($subject_name) }}</td>
        <td></td>
        <td colspan="2" style="font-weight: bold; text-align: right;">NAMA GURU: {{ strtoupper($teacher->name) }}</td>
    </tr>
    <tr>
        <td colspan="5"></td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000;">Tanggal</th>
            <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: left; border: 1px solid #000000;">Nama Siswa</th>
            <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000;">NISN</th>
            <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000;">Kelas</th>
            <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000;">Status Kehadiran</th>
        </tr>
    </thead>
    <tbody>
        @foreach($attendances as $att)
        <tr>
            <td style="text-align: center; border: 1px solid #000000;">{{ date('d-m-Y', strtotime($att->date)) }}</td>
            <td style="text-align: left; border: 1px solid #000000;">{{ $att->student->name }}</td>
            <td style="text-align: center; border: 1px solid #000000;">{{ $att->student->nisn ?? '-' }}</td>
            <td style="text-align: center; border: 1px solid #000000;">{{ $att->class->name }}</td>
            <td style="text-align: center; border: 1px solid #000000;">{{ $att->status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
