<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRule;
use Illuminate\Http\Request;

class AttendanceRuleController extends Controller
{
    // Tampilan daftar aturan
    public function index()
    {
        $rules = AttendanceRule::all();
        return view('pages.attendance-rules.index', compact('rules'));
    }

    // Proses update banyak aturan sekaligus
    public function update(Request $request)
    {
        // Cek apakah data rules ada
        if (!$request->has('rules')) {
            return response()->json(['message' => 'Data tidak ditemukan'], 400);
        }

        foreach ($request->rules as $id => $value) {
            \App\Models\AttendanceRule::where('id', $id)->update([
                'rule_value' => $value
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Berhasil diperbarui']);
    }
}
