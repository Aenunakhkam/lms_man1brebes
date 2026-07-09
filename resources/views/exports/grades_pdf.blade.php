<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Nilai Siswa</title>
    <style>
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 11px; 
            color: #333;
            margin: 0;
            padding: 0;
        }
        .kop-surat {
            border-bottom: 3px double #000;
            margin-bottom: 20px;
            padding-bottom: 10px;
            text-align: center;
        }
        .kop-surat h1 { margin: 0; font-size: 20px; text-transform: uppercase; }
        .kop-surat h2 { margin: 5px 0; font-size: 16px; }
        .kop-surat p { margin: 2px 0; font-style: italic; font-size: 10px; }

        .title { text-align: center; margin-bottom: 20px; }
        .title h3 { margin: 0; font-size: 14px; text-decoration: underline; text-transform: uppercase; }

        .info { margin-bottom: 15px; }
        .info table { width: 100%; border: none; }
        .info td { padding: 2px 0; border: none; font-weight: bold; }

        .grade-table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed; /* Ensures even column distribution */
        }
        .grade-table th, .grade-table td { 
            border: 1px solid #000; 
            padding: 6px 4px; 
            text-align: center; 
            word-wrap: break-word; /* Prevents overflow */
        }
        .grade-table th { 
            background-color: #f0f4f8; 
            font-size: 9px;
            text-transform: uppercase;
        }
        .grade-table td { font-size: 10px; }
        .text-left { text-align: left !important; padding-left: 8px !important; }
        
        .name-col { width: 25%; }
        .no-col { width: 30px; }
        .final-col { width: 60px; background-color: #fff9c4 !important; font-weight: bold; }

        .footer { 
            position: fixed; 
            bottom: -15px; 
            left: 0px; 
            right: 0px;
            width: 100%;
            border-top: 1px dashed #ccc;
            padding-top: 5px;
            font-size: 9px;
            color: #666;
        }
        
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <div class="kop-surat">
        <h1>{{ $school_name }}</h1>
        <p>{{ $school_address }} | Telp: {{ $school_phone }}</p>
        <p>Website: {{ $school_website }}</p>
    </div>

    <div class="title">
        <h3>REKAPITULASI NILAI {{ strtoupper($subject->name) }}</h3>
    </div>

    <div class="info">
        <table cellspacing="0" cellpadding="0">
            <tr>
                <td width="15%">KELAS</td>
                <td width="2%">:</td>
                <td width="33%">{{ strtoupper($class->name) }}</td>
                <td width="15%">TAHUN PELAJARAN</td>
                <td width="2%">:</td>
                <td width="33%">{{ strtoupper($academic_year) }}</td>
            </tr>
            <tr>
                <td>GURU PENGAMPU</td>
                <td>:</td>
                <td>{{ strtoupper($teacher->name) }}</td>
                <td>SEMESTER</td>
                <td>:</td>
                <td>{{ strtoupper($semester) }}</td>
            </tr>
        </table>
    </div>

    <table class="grade-table">
        <thead>
            <tr>
                <th class="no-col" style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center;">NO</th>
                <th class="name-col text-left" style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: left;">NAMA SISWA</th>
                @foreach($categories as $cat)
                    <th style="background-color: #203764; color: #FFFFFF; font-weight: bold; text-align: center;">{{ strtoupper($cat) }}</th>
                @endforeach
                <th class="final-col" style="color: #000000; font-weight: bold; text-align: center;">RATA-RATA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td class="text-left" style="text-align: left;">{{ strtoupper($student->name) }}</td>
                    @php $total = 0; $count = 0; @endphp
                    @foreach($categories as $cat)
                        @php 
                            $grade = $grades->where('student_id', $student->id)->where('grade_type', $cat)->first();
                            $score = $grade ? $grade->score : 0;
                            if($grade) { $total += $score; $count++; }
                        @endphp
                        <td style="text-align: center;">{{ $grade ? number_format($score, 0) : '-' }}</td>
                    @endforeach
                    <td class="final-col" style="background-color: #fff9c4; font-weight: bold; text-align: center;">
                        {{ $count > 0 ? number_format($total / $count, 1) : '0' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p><i>Dokumen ini dicetak secara otomatis oleh sistem {{ $school_name }} pada tanggal {{ date('d-m-Y H:i:s') }}.</i></p>
    </div>
</body>
</html>
