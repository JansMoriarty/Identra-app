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

    // Tambahkan ini di AttendanceRuleController.php
    public function getSettings()
    {
        // Ambil data langsung berdasarkan kolom 'name' di database Anda
        return response()->json([
            // Di screenshot DB Anda namanya 'batas_masuk', bukan 'jam_masuk'
            'jam_masuk' => \App\Models\AttendanceRule::getValue('batas_masuk'),
            'jam_pulang' => \App\Models\AttendanceRule::getValue('jam_pulang'),
        ]);
    }
}
