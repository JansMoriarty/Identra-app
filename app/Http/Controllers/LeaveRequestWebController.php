<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest; // Sesuaikan dengan nama model izin kamu
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LeaveRequestWebController extends Controller
{
    public function index()
    {
        $requests = LeaveRequest::with('guru')->latest()->paginate(10);
        // Sesuaikan path view dengan folder kamu
        return view('pages.leave_request.index', compact('requests'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,disetujui,ditolak'
        ]);

        $leave = LeaveRequest::findOrFail($id);

        // Simpan status lama untuk pengecekan perubahan
        $statusLama = $leave->status;

        // Update status izin
        $leave->update([
            'status' => $request->status
        ]);

        // LOGIKA SINKRONISASI KE TABEL ATTENDANCES
        if ($request->status === 'disetujui') {
            // 1. Buat rentang tanggal (CarbonPeriod)
            // Ini akan menghandle jika izin lebih dari 1 hari
            $period = CarbonPeriod::create($leave->tanggal_mulai, $leave->tanggal_selesai);

            foreach ($period as $date) {
                $tanggalStr = $date->format('Y-m-d');

                // 2. Gunakan updateOrCreate agar tidak duplikat
                // Jika hari itu gurunya sudah ada data (misal status Alpha), akan ditimpa jadi Izin
                Attendance::updateOrCreate(
                    [
                        'guru_id' => $leave->guru_id,
                        'tanggal' => $tanggalStr,
                    ],
                    [
                        'leave_request_id' => $leave->id, // Relasi yang baru kita buat di migrasi
                        'status'           => ($leave->jenis === 'sakit') ? 'sakit' : 'izin',
                        'metode'           => 'manual',
                        'keterangan'       => 'Izin disetujui: ' . ($leave->keterangan ?? $leave->jenis),
                        'jam_masuk'        => null, // Izin tidak punya jam masuk
                        'jam_pulang'       => null,
                    ]
                );
            }
        }

        // JIKA STATUS DIUBAH DARI 'DISETUJUI' MENJADI LAINNYA (Dibatalkan/Ditolak)
        elseif ($statusLama === 'disetujui' && $request->status !== 'disetujui') {
            // Hapus data absen yang berkaitan dengan ID izin ini
            Attendance::where('leave_request_id', $leave->id)->delete();
        }

        return back()->with('success', 'Status izin berhasil diperbarui dan disinkronkan ke absensi.');
    }
}
