<?php

namespace App\Http\Controllers;

use App\Models\FlexibilityItem;
use Illuminate\Http\Request;

class FlexibilityItemController extends Controller
{
    public function index()
    {
        $items = FlexibilityItem::latest()->get();
        return view('pages.flexibility.index', compact('items'));
    }

    public function create()
    {
        return view('pages.flexibility.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name'   => 'required|string|max:255',
            'description' => 'required',
            'item_type'   => 'required|in:LATE_WAVER,WFH_PASS,LEAVE_PERMISSION',
            'value_power' => 'required|integer',
            'point_cost'  => 'required|integer',
            'stock_limit' => 'nullable|integer',
        ]);

        $validated['is_available'] = true; // Default aktif saat dibuat

        FlexibilityItem::create($validated);

        return redirect()->route('vouchers.index')->with('success', 'Voucher berhasil dibuat! 🚀');
    }

    public function edit($id)
    {
        $item = FlexibilityItem::findOrFail($id);
        return view('pages.flexibility.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = FlexibilityItem::findOrFail($id);

        $validated = $request->validate([
            'item_name'   => 'required|string|max:255',
            'description' => 'required',
            'item_type'   => 'required|in:LATE_WAVER,WFH_PASS,LEAVE_PERMISSION',
            'value_power' => 'required|integer',
            'point_cost'  => 'required|integer',
            'stock_limit' => 'nullable|integer',
        ]);

        $item->update($validated);

        return redirect()->route('vouchers.index')->with('success', 'Voucher diperbarui! ✅');
    }

    public function destroy($id)
    {
        try {
            $item = FlexibilityItem::findOrFail($id);

            // Laravel otomatis tahu ini soft delete karena kita sudah pasang di Model
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Voucher berhasil dinonaktifkan (Soft Delete) ✅'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }
}
