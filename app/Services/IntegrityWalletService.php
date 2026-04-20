<?php

namespace App\Services;

use App\Models\PointRule;
use App\Models\PointLedger;
use App\Models\UserToken;
use App\Models\Attendance;
use Symfony\Component\HttpFoundation\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IntegrityWalletService
{
    /**
     * TAHAP 1: Interceptor - Cek apakah user punya token kelonggaran
     */
    public function checkAndApplyToken($userId, $type, $lateMinutes = 0)
    {
        // Cari token yang tersedia untuk kompensasi telat
        $token = UserToken::where('user_id', $userId)
            ->where('status', 'AVAILABLE')
            ->whereHas('item', function ($query) use ($type) {
                $query->where('item_type', $type);
            })
            ->first();

        if ($token && $lateMinutes <= $token->item->value_power) {
            return $token;
        }

        return null;
    }

    /**
     * TAHAP 2: Rule Engine - Hitung & Catat Poin Otomatis
     */
    public function processPoints($userId, $attendanceId, $event, $currentTime)
    {
        $user = \App\Models\User::find($userId);
        $role = $user->role; // Misal: 'guru'

        // Ambil aturan yang sesuai
        $rules = PointRule::where('target_role', $role)
            ->where('trigger_event', $event)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();

        foreach ($rules as $rule) {
            if ($this->isConditionMet($rule, $currentTime)) {
                $this->createLedgerEntry(
                    $userId,
                    $rule->point_modifier,
                    $rule->point_modifier > 0 ? 'EARN' : 'PENALTY',
                    "Poin dari aturan: " . $rule->rule_name,
                    $attendanceId
                );

                // Update poin di tabel attendances untuk history
                Attendance::where('id', $attendanceId)->update([
                    'points_earned' => $rule->point_modifier
                ]);

                break; // Stop di aturan pertama yang terpenuhi (berdasarkan priority)
            }
        }
    }

    // Tambahkan operator yang lebih lengkap untuk mendukung Rule Engine yang dinamis
    private function isConditionMet($rule, $currentTime)
    {
        $checkTime = Carbon::parse($currentTime);
        // Ambil batas masuk dinamis dari setting sekolah
        $batasMasukSetting = \App\Models\AttendanceRule::getValue('batas_masuk', '08:00:00');
        $batasMasuk = Carbon::parse($batasMasukSetting);

        // 1. Kondisi berbasis perbandingan dengan BATAS MASUK dinamis
        // Jika di database point_rules, kolom condition_time diisi NULL atau 'DYNAMIC'
        if ($rule->condition_time === null || $rule->condition_time === 'DYNAMIC') {
            switch ($rule->condition_operator) {
                case '<':
                    return $checkTime->lt($batasMasuk);  // Datang sebelum jam masuk
                case '>':
                    return $checkTime->gt($batasMasuk);  // Datang setelah jam masuk (telat)
            }
        }

        // 2. Kondisi berbasis Jam Statis (Tetap pertahankan ini untuk rule khusus)
        // Misal: Rule "Super Early" khusus untuk yang datang sebelum jam 06:00
        if ($rule->condition_time && $rule->condition_time !== 'DYNAMIC') {
            $conditionTime = Carbon::parse($rule->condition_time);
            switch ($rule->condition_operator) {
                case '<':
                    return $checkTime->lt($conditionTime);
                case '>':
                    return $checkTime->gt($conditionTime);
                case '<=':
                    return $checkTime->lte($conditionTime);
                case '>=':
                    return $checkTime->gte($conditionTime);
            }
        }

        // 3. Kondisi berbasis Menit Terlambat (Sudah Oke)
        if ($rule->condition_minute !== null) {
            $lateMinutes = $checkTime->gt($batasMasuk) ? $checkTime->diffInMinutes($batasMasuk) : 0;
            switch ($rule->condition_operator) {
                case '>':
                    return $lateMinutes > $rule->condition_minute;
                case '>=':
                    return $lateMinutes >= $rule->condition_minute;
                case '<':
                    return $lateMinutes < $rule->condition_minute;
            }
        }

        return false;
    }

    private function createLedgerEntry($userId, $amount, $type, $desc, $refId)
    {
        DB::transaction(function () use ($userId, $amount, $type, $desc, $refId) {
            $lastLedger = PointLedger::where('user_id', $userId)->latest()->first();
            $lastBalance = $lastLedger ? $lastLedger->current_balance : 0;

            PointLedger::create([
                'user_id' => $userId,
                'transaction_type' => $type,
                'amount' => $amount,
                'current_balance' => $lastBalance + $amount,
                'description' => $desc,
                'reference_id' => $refId
            ]);
        });
    }

    public function useToken($tokenId, $attendanceId)
    {
        UserToken::where('id', $tokenId)->update([
            'status' => 'USED',
            'used_at_attendance_id' => $attendanceId,
            'used_at' => now()
        ]);
    }

    public function applyLateWaver($userId, $lateMinutes)
    {
        // Gunakan join agar bisa sorting berdasarkan value_power milik flexibility_items
        $token = UserToken::where('user_id', $userId)
            ->where('status', 'AVAILABLE')
            ->join('flexibility_items', 'user_tokens.item_id', '=', 'flexibility_items.id')
            ->where('flexibility_items.item_type', 'LATE_WAVER')
            ->where('flexibility_items.value_power', '>=', $lateMinutes)
            ->orderBy('flexibility_items.value_power', 'asc') // Ambil yang paling kecil tapi cukup
            ->select('user_tokens.*') // Pastikan hanya ambil kolom user_tokens
            ->first();

        if ($token) {
            $token->update([
                'status' => 'USED',
                'used_at' => now(),
                'description' => "Menutupi keterlambatan $lateMinutes menit"
            ]);
            return true;
        }
        return false;
    }

    /**
     * WFH PASS: Bypass logika absensi
     */
    public function checkWfhPass($userId)
    {
        $token = UserToken::where('user_id', $userId)
            ->where('status', 'AVAILABLE')
            ->whereHas('item', function ($q) {
                $q->where('item_type', 'WFH_PASS');
            })
            ->first();

        if ($token) {
            $token->update(['status' => 'USED', 'used_at' => now()]);
            return true;
        }
        return false;
    }

    public function storeAttendance(Request $request)
    {
        if ($request->use_wfh_token) {
            // 1. Validasi apakah benar punya token WFH
            $token = UserToken::where('user_id', $request->user_id)
                ->where('status', 'AVAILABLE')
                ->whereHas('item', fn($q) => $q->where('item_type', 'WFH_PASS'))
                ->first();

            if ($token) {
                // 2. Bypass koordinat & wajah
                $token->update(['status' => 'USED', 'used_at' => now()]);
                return $this->processSuccessAttendance($request, 'WFH');
            }
        }

        // 3. Jalankan validasi lokasi & wajah normal jika tidak pakai token
        return $this->normalAttendanceValidation($request);
    }
}
