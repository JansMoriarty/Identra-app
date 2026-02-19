<!DOCTYPE html>
<html>
<head>
    <title>Laporan Absensi - {{ $guru->name }}</title>
    <style>
        @page { size: A4; margin: 1.5cm; }
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.4; font-size: 11px; }
        
        /* Header */
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #1a56db; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #1a56db; text-transform: uppercase; font-size: 18px; }
        .header p { margin: 3px 0; color: #666; font-size: 12px; }

        /* Info Section */
        .info-container { margin-bottom: 20px; width: 100%; }
        .info-table { width: 100%; border: none; }
        .info-table td { padding: 2px 0; vertical-align: top; border: none !important; }
        .label { font-weight: bold; width: 100px; }

        /* Summary Box */
        .summary-table { width: 100%; margin-bottom: 25px; border-collapse: collapse; }
        .summary-table th { background-color: #f8fafc; color: #475569; border: 1px solid #e2e8f0; padding: 8px; text-transform: uppercase; font-size: 9px; }
        .summary-table td { border: 1px solid #e2e8f0; padding: 10px; text-align: center; font-size: 14px; font-weight: bold; }
        
        /* Badge Colors in Summary */
        .text-hadir { color: #059669; }
        .text-telat { color: #d97706; }
        .text-izin { color: #2563eb; }
        .text-sakit { color: #7c3aed; }
        .text-alpha { color: #dc2626; }

        /* Main Table */
        .main-table { width: 100%; border-collapse: collapse; }
        .main-table th { background-color: #1a56db; color: white; padding: 10px; border: 1px solid #1a56db; text-align: center; }
        .main-table td { padding: 8px; border: 1px solid #e2e8f0; text-align: center; }
        .main-table tr:nth-child(even) { background-color: #fcfcfc; }

        /* Footer */
        .footer-container { margin-top: 40px; width: 100%; }
        .footer-table { width: 100%; border: none; }
        .footer-table td { border: none !important; text-align: center; width: 50%; }
        .signature-space { margin-top: 60px; font-weight: bold; text-decoration: underline; }
        .print-date { font-size: 9px; color: #94a3b8; margin-top: 20px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Rekapitulasi Presensi Guru</h2>
        <p>SMK Negeri 1 Cianjur</p>
        <p style="font-size: 10px;">Jl. Raya Karangtengah, Cianjur, Jawa Barat</p>
    </div>

    <div class="info-container">
        <table class="info-table">
            <tr>
                <td class="label">Nama Guru</td>
                <td>: {{ $guru->name }}</td>
                <td class="label">Periode</td>
                <td>: {{ \Carbon\Carbon::parse($start)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($end)->translatedFormat('d M Y') }}</td>
            </tr>
            <tr>
                <td class="label">NIP</td>
                <td>: {{ $guru->nip ?? '-' }}</td>
                <td class="label">Total Hari</td>
                <td>: {{ $attendances->count() }} Hari Kerja</td>
            </tr>
        </table>
    </div>

    <table class="summary-table">
        <thead>
            <tr>
                <th>Tepat Waktu</th>
                <th>Terlambat</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Tanpa Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-hadir">{{ $summary['hadir'] }}</td>
                <td class="text-telat">{{ $summary['telat'] }}</td>
                <td class="text-izin">{{ $summary['izin'] }}</td>
                <td class="text-sakit">{{ $summary['sakit'] }}</td>
                <td class="text-alpha">{{ $summary['alpha'] }}</td>
            </tr>
        </tbody>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Tanggal</th>
                <th width="15%">Jam Masuk</th>
                <th width="15%">Jam Pulang</th>
                <th width="15%">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('l, d M Y') }}</td>
                    <td>{{ $row->jam_masuk ?? '--:--' }}</td>
                    <td>{{ $row->jam_pulang ?? '--:--' }}</td>
                    <td style="font-weight: bold;">
                        @php
                            $status = strtolower($row->status);
                        @endphp
                        <span class="text-{{ $status == 'hadir' ? 'hadir' : ($status == 'telat' ? 'telat' : 'alpha') }}">
                            {{ strtoupper($row->status) }}
                        </span>
                    </td>
                    <td>{{ $row->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 30px; color: #94a3b8;">Data absensi tidak ditemukan untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-container">
        <table class="footer-table">
            <tr>
                <td></td>
                <td>
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
        * Dokumen ini dicetak otomatis melalui Sistem Informasi Presensi pada {{ date('d/m/Y H:i') }}
    </div>

</body>
</html>