<!DOCTYPE html>
<html>

<head>
    <title>Rekapitulasi Presensi Keseluruhan</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.2cm;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            line-height: 1.4;
            font-size: 10px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1a56db;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            color: #1a56db;
            text-transform: uppercase;
            font-size: 16px;
        }

        .header p {
            margin: 2px 0;
            color: #666;
            font-size: 11px;
        }

        /* Info Section */
        .info-summary {
            margin-bottom: 15px;
            width: 100%;
            font-size: 11px;
        }

        /* Main Table */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .main-table th {
            background-color: #1a56db;
            color: white;
            padding: 8px;
            border: 1px solid #1a56db;
            text-align: center;
            text-transform: uppercase;
            font-size: 9px;
        }

        .main-table td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .main-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-left {
            text-align: left !important;
        }

        .font-bold {
            font-weight: bold;
        }

        /* Status Colors */
        .text-hadir {
            color: #059669;
            font-weight: bold;
        }

        .text-telat {
            color: #d97706;
            font-weight: bold;
        }

        .text-izin {
            color: #2563eb;
            font-weight: bold;
        }

        .text-sakit {
            color: #7c3aed;
            font-weight: bold;
        }

        .text-alpha {
            color: #dc2626;
            font-weight: bold;
        }

        /* Footer */
        .footer-container {
            margin-top: 30px;
            width: 100%;
        }

        .footer-table {
            width: 100%;
            border: none;
        }

        .footer-table td {
            border: none !important;
            text-align: center;
            width: 70%;
        }

        .signature-box {
            text-align: center;
            width: 30%;
        }

        .signature-space {
            margin-top: 50px;
            font-weight: bold;
            text-decoration: underline;
        }

        .print-date {
            font-size: 8px;
            color: #94a3b8;
            margin-top: 15px;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>Rekapitulasi Presensi Guru (Keseluruhan)</h2>
        <p>SMK Negeri 1 Cianjur</p>
        <p style="font-size: 11px; color: #1a56db; font-weight: bold;">
            Periode: {{ \Carbon\Carbon::parse($start)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($end)->translatedFormat('d F Y') }}
        </p>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th class="text-left" width="22%">Nama Guru / NIP</th>
                <th width="10%">Hadir (Tepat)</th>
                <th width="10%">Terlambat</th>
                <th width="8%">Izin</th>
                <th width="8%">Sakit</th>
                <th width="10%">Alpha</th>
                <th width="12%">Total Masuk</th>
                <th width="10%">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekap as $index => $row)
            @php
            $totalMasuk = $row->total_hadir + $row->total_telat;
            // Hitung persentase sederhana
            $diffInDays = \Carbon\Carbon::parse($start)->diffInDays(\Carbon\Carbon::parse($end)) ?: 1;
            $persentase = round(($totalMasuk / ($diffInDays + 1)) * 100);
            if($persentase > 100) $persentase = 100;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left">
                    <div class="font-bold">{{ $row->name }}</div>
                    <div style="color: #64748b; font-size: 8px;">NIP. {{ $row->nip ?? '-' }}</div>
                </td>
                <td class="text-hadir">{{ $row->total_hadir }}</td>
                <td class="text-telat">{{ $row->total_telat }}</td>
                <td class="text-izin">{{ $row->total_izin }}</td>
                <td class="text-sakit">{{ $row->total_sakit }}</td>
                <td class="text-alpha">{{ $row->total_alpha }}</td>
                <td class="font-bold">{{ $totalMasuk }} Hari</td>
                <td class="font-bold" style="color: {{ $persentase >= 80 ? '#059669' : ($persentase >= 50 ? '#d97706' : '#dc2626') }}">
                    {{ $persentase }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-container">
        <table class="footer-table">
            <tr>
                <td></td>
                <td class="signature-box">
                    <p>Cianjur, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p>Mengetahui,</p>
                    <p>Kepala Sekolah</p>
                    <div class="signature-space">
                        ( ......................................... )
                    </div>
                    <p>NIP. .....................................</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="print-date">
        * Dokumen Rekapitulasi Kolektif dicetak pada {{ date('d/m/Y H:i') }}
    </div>

</body>

</html>