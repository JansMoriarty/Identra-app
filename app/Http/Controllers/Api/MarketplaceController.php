<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FlexibilityItem;
use App\Models\UserToken;
use App\Models\PointLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MarketplaceController extends Controller
{
    /**
     * GET: Marketplace (Quota-based, bukan stok)
     */
    public function getVouchers(Request $request)
    {
        $user = $request->user();

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $items = FlexibilityItem::where('is_available', true)->get();

        $data = $items->map(function ($item) use ($user, $startOfMonth, $endOfMonth) {

            $used = UserToken::where('user_id', $user->id)
                ->where('item_id', $item->id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();

            $limit = $item->stock_limit; // sekarang artinya monthly_limit

            $remaining = $limit !== null
                ? max(0, $limit - $used)
                : null;

            $isLimitReached = $limit !== null && $remaining <= 0;

            return [
                'id' => $item->id,
                'item_name' => $item->item_name,
                'description' => $item->description,
                'item_type' => $item->item_type,
                'value_power' => $item->value_power,
                'point_cost' => $item->point_cost,

                // 🔥 QUOTA INFO
                'monthly_limit' => $limit,
                'used_this_month' => $used,
                'remaining_quota' => $remaining,
                'is_limit_reached' => $isLimitReached,

                'is_available' => $item->is_available,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * POST: Redeem Voucher (Quota-based)
     */
    public function redeemVoucher(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:flexibility_items,id'
        ]);

        $user = $request->user();
        $item = FlexibilityItem::findOrFail($request->item_id);

        // Ambil saldo terakhir
        $lastLedger = PointLedger::where('user_id', $user->id)->latest()->first();
        $currentBalance = $lastLedger ? $lastLedger->current_balance : 0;

        if ($currentBalance < $item->point_cost) {
            return response()->json([
                'success' => false,
                'message' => 'Poin tidak mencukupi!'
            ], 422);
        }

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        try {
            DB::beginTransaction();

            /**
             * 🔥 VALIDASI QUOTA PER USER PER BULAN
             */
            $usedThisMonth = UserToken::where('user_id', $user->id)
                ->where('item_id', $item->id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->lockForUpdate()
                ->count();

            if ($item->stock_limit !== null && $usedThisMonth >= $item->stock_limit) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Limit bulan ini sudah habis.'
                ], 422);
            }

            /**
             * 💰 POTONG POIN
             */
            PointLedger::create([
                'user_id' => $user->id,
                'transaction_type' => 'SPEND',
                'amount' => -$item->point_cost,
                'current_balance' => $currentBalance - $item->point_cost,
                'description' => "Penukaran: " . $item->item_name,
            ]);

            /**
             * 🎟️ SIMPAN TOKEN
             */
            UserToken::create([
                'user_id' => $user->id,
                'item_id' => $item->id,
                'status' => 'AVAILABLE',
                'expires_at' => Carbon::now()->addMonths(3),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Voucher berhasil ditukar!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    /**
     * GET: Inventory user
     */
    public function myTokens(Request $request)
    {
        $tokens = UserToken::with('item')
            ->where('user_id', $request->user()->id)
            ->orderBy('status', 'asc')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tokens
        ]);
    }

    /**
     * GET: Riwayat poin
     */
    public function pointHistory(Request $request)
    {
        $history = PointLedger::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }
}