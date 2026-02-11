<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LeaveRequestController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis'           => 'required|in:izin,sakit,cuti',
            'alasan'          => 'required|string',
            'lampiran_foto'   => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Handle Upload File
        $fotoPath = null;
        if ($request->hasFile('lampiran_foto')) {
            $fotoPath = $request->file('lampiran_foto')->store('leave_attachments', 'public');
        }

        // 3. Simpan ke Database
        $leave = LeaveRequest::create([
            // UBAH BARIS INI: Gunakan guru_id karena di model User kamu kolomnya adalah guru_id
            'guru_id'         => auth()->user()->guru_id, 
            'tanggal_mulai'   => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jenis'           => $request->jenis,
            'alasan'          => $request->alasan,
            'lampiran_foto'   => $fotoPath,
            'status'          => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil dikirim!',
            'data'    => $leave
        ], 201);
    }
}