<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\ClassAttendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportClassController extends Controller
{
    public function index(Request $request)
    {
        // Ambil filter tanggal (default hari ini)
        $date = $request->get('date', Carbon::today()->toDateString());
        $dayName = Carbon::parse($date)->locale('id')->isoFormat('dddd');

        // Ambil semua jadwal di hari tersebut
        $schedules = Schedule::with(['user', 'classroom', 'subject'])
            ->where('day', $dayName)
            ->get();

        // Ambil data absensi yang sudah masuk di tanggal tersebut
        $attendances = ClassAttendance::whereDate('tanggal', $date)->get()->keyBy('schedule_id');

        // Gabungkan data (Mapping)
        $rekapSesi = $schedules->map(function ($schedule) use ($attendances, $dayName) {
            $auth = $attendances->get($schedule->id);

            return [
                'id' => $schedule->id,
                'jam_mulai' => Carbon::parse($schedule->start_time)->format('H:i'),
                'jam_selesai' => Carbon::parse($schedule->end_time)->format('H:i'),
                'hari' => $dayName,
                'nama_guru' => $schedule->user->name,
                'nama_mapel' => $schedule->subject->name,
                'nama_kelas' => $schedule->classroom->name,
                'status' => $auth ? 'hadir' : 'kosong',
                'is_telat' => $auth ? $this->checkIsTelat($schedule->start_time, $auth->created_at) : false,
                'menit_telat' => $auth ? $this->calculateDelay($schedule->start_time, $auth->created_at) : 0,
                'materi' => $auth ? $auth->materi : null,
            ];
        });

        return view('pages.report-class.index', [
            'rekapSesi' => $rekapSesi
        ]);
    }

    private function checkIsTelat($startTime, $attendanceTime)
    {
        $start = Carbon::parse($startTime);
        $auth = Carbon::parse($attendanceTime);
        // Toleransi 15 menit misalnya
        return $auth->gt($start->addMinutes(15));
    }

    private function calculateDelay($startTime, $attendanceTime)
    {
        $start = Carbon::parse($startTime);
        $auth = Carbon::parse($attendanceTime);
        return $auth->gt($start) ? $auth->diffInMinutes($start) : 0;
    }

    
}