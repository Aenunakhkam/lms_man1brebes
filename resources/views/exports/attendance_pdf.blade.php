<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Presensi Siswa</title>
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

        .attendance-table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        .attendance-table th, .attendance-table td { 
            border: 1px solid #000; 
            padding: 6px 4px; 
            text-align: center; 
        }
        .attendance-table th { 
            background-color: #f0f4f8; 
            font-size: 10px;
            text-transform: uppercase;
        }
        .text-left { text-align: left !important; padding-left: 8px !important; }
        
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
        
        @page { margin: 1cm; }
    </style>
</head>
<body>
    <div class="kop-surat">
        <h1>{{ $school_name }}</h1>
        <p>{{ $school_address }} | Telp: {{ $school_phone }}</p>
        <p>Website: {{ $school_website }}</p>
    </div>

    <div class="title">
        <h3>REKAPITULASI PRESENSI SISWA</h3>
    </div>

    <div class="info">
        <table cellspacing="0" cellpadding="0">
            <tr>
                <td width="15%">KELAS</td>
                <td width="2%">:</td>
                <td width="33%">{{ strtoupper($class->name) }}</td>
                <td width="15%">TAHUN PELAJARAN</td>
                <td width="2%">:</td>
                <td width="33%">{{ date('Y') }}/{{ date('Y') + 1 }}</td>
            </tr>
            <tr>
                <td>PERIODE</td>
                <td>:</td>
                <td>{{ strtoupper($period) }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="35%" class="text-left">NAMA SISWA</th>
                <th width="15%">HADIR</th>
                <th width="15%">IZIN</th>
                <th width="15%">SAKIT</th>
                <th width="15%">ALPA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
                @php 
                    $studentAtt = $attendances->get($student->id, collect());
                    $hadir = $studentAtt->where('status', 'Hadir')->count();
                    $izin = $studentAtt->where('status', 'Izin')->count();
                    $sakit = $studentAtt->where('status', 'Sakit')->count();
                    $alpa = $studentAtt->where('status', 'Alpa')->count();
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="text-left">{{ strtoupper($student->name) }}</td>
                    <td>{{ $hadir }}</td>
                    <td>{{ $izin }}</td>
                    <td>{{ $sakit }}</td>
                    <td>{{ $alpa }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p><i>Dokumen ini dicetak secara otomatis oleh sistem {{ $school_name }} pada tanggal {{ date('d-m-Y H:i:s') }}.</i></p>
    </div>
</body>
</html>
