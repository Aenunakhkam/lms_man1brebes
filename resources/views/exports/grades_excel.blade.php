<table>
    <tr>
        <td colspan="7" style="font-size: 16px; font-weight: bold; text-align: center;">{{ $school_name }}</td>
    </tr>
    <tr>
        <td colspan="7" style="font-size: 10px; text-align: center;">{{ $school_address }} | Telp: {{ $school_phone }}</td>
    </tr>
    <tr>
        <td colspan="7" style="font-size: 10px; text-align: center;">Website: {{ $school_website }}</td>
    </tr>
    <tr>
        <td colspan="7" style="font-size: 14px; font-weight: bold; text-align: center; text-decoration: underline;">REKAPITULASI NILAI {{ strtoupper($subject->name) }}</td>
    </tr>
    <tr>
        <td colspan="3" style="font-weight: bold; text-align: left;">KELAS: {{ strtoupper($class->name) }}</td>
        <td></td>
        <td colspan="3" style="font-weight: bold; text-align: right;">TAHUN PELAJARAN: {{ strtoupper($academic_year) }}</td>
    </tr>
    <tr>
        <td colspan="3" style="font-weight: bold; text-align: left;">GURU PENGAMPU: {{ strtoupper($teacher->name) }}</td>
        <td></td>
        <td colspan="3" style="font-weight: bold; text-align: right;">SEMESTER: {{ strtoupper($semester) }}</td>
    </tr>
    <tr>
        <td colspan="7"></td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000;">NO</th>
            <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: left; border: 1px solid #000000;">NAMA SISWA</th>
            @foreach($categories as $cat)
                <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000;">{{ strtoupper($cat) }}</th>
            @endforeach
            <th style="background-color: #fff9c4; color: #000000; font-weight: bold; text-align: center; border: 1px solid #000000;">RATA-RATA</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $index => $student)
            <tr>
                <td style="text-align: center; border: 1px solid #000000;">{{ $loop->iteration }}</td>
                <td style="text-align: left; border: 1px solid #000000;">{{ strtoupper($student->name) }}</td>
                @php $total = 0; $count = 0; @endphp
                @foreach($categories as $cat)
                    @php 
                        $grade = $grades->where('student_id', $student->id)->where('grade_type', $cat)->first();
                        $score = $grade ? $grade->score : 0;
                        if($grade) { $total += $score; $count++; }
                    @endphp
                    <td style="text-align: center; border: 1px solid #000000;">{{ $grade ? number_format($score, 0) : '-' }}</td>
                @endforeach
                <td style="background-color: #fff9c4; font-weight: bold; text-align: center; border: 1px solid #000000;">
                    {{ $count > 0 ? number_format($total / $count, 1) : '0' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
