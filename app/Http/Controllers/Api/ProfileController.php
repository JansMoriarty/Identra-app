<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    // app/Http/Controllers/Api/ProfileController.php

    public function getSummary(Request $request)
    {
        $user = $request->user();

        // 1. Ambil saldo terakhir dari Ledger
        $currentBalance = \App\Models\PointLedger::where('user_id', $user->id)
            ->latest()
            ->value('current_balance') ?? 0;

        // 2. Logic Rank (Sederhana)
        $rank = "Guru Muda";
        if ($currentBalance >= 1000) $rank = "Guru Teladan";
        else if ($currentBalance >= 500) $rank = "Guru Senior";

        return response()->json([
            'success' => true,
            'data' => [
                'name'           => $user->name,
                'current_points' => $currentBalance,
                'rank_name'      => $rank,
                'next_target'    => 1000, // Target poin ke level berikutnya
            ]
        ]);
    }
}
