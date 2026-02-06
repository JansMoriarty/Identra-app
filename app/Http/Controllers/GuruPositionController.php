<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\GuruPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuruPositionController extends Controller
{
    /**
     * Menampilkan daftar semua plotting jabatan
     */
    public function index()
    {
        $assignments = GuruPosition::with('position')
            ->latest()
            ->get()
            ->map(function ($item) {
                // Cari nama guru berdasarkan guru_id
                $guru = \App\Models\User::where('guru_id', $item->guru_id)->first();

                return [
                    'id' => $item->id,
                    'guru_id' => $item->guru_id,
                    'guru_nama' => $guru?->name ?? 'Guru tidak ditemukan', // 🔥 PENTING
                    'position' => [
                        'nama_jabatan' => $item->position?->nama_jabatan,
                    ],
                    'tanggal_mulai' => $item->tanggal_mulai,
                    'tanggal_selesai' => $item->tanggal_selesai,
                    'is_active' => $item->is_active,
                ];
            });

        return view('pages.guru_positions.index', compact('assignments'));
    }


    /**
     * Menampilkan form plotting (butuh data master jabatan)
     */
    public function create()
    {
        $positions = Position::all();
        return view('pages.guru_positions.create', compact('positions'));
    }

    /**
     * Simpan penugasan baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guru_id'       => 'required|uuid', // ID dari API
            'position_id'   => 'required|exists:positions,id',
            'tanggal_mulai' => 'required|date',
            'keterangan'    => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // 1. Set semua jabatan aktif sebelumnya menjadi non-aktif untuk guru ini
            GuruPosition::where('guru_id', $request->guru_id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'tanggal_selesai' => $request->tanggal_mulai // Selesai saat yang baru mulai
                ]);

            // 2. Simpan jabatan baru
            $validated['is_active'] = true;
            GuruPosition::create($validated);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Jabatan berhasil di-plot!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal simpan data.'], 500);
        }
    }

    /**
     * Menampilkan form edit
     */
    public function edit(GuruPosition $guruPosition)
    {
        $positions = Position::all();

        $guru = \App\Models\User::where('guru_id', $guruPosition->guru_id)->first();

        return view('pages.guru_positions.edit', [
            'guruPosition' => $guruPosition,
            'positions' => $positions,
            'guru_nama' => $guru?->name ?? 'Guru tidak ditemukan',
        ]);
    }


    /**
     * Update data penugasan
     */
    public function update(Request $request, GuruPosition $guruPosition)
    {
        $validated = $request->validate([
            'position_id'     => 'required|exists:positions,id',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'is_active'       => 'required|boolean'
        ]);

        try {
            DB::beginTransaction();

            // Jika jabatan ini dibuat aktif, nonaktifkan jabatan lain milik guru yang sama
            if ($validated['is_active']) {
                GuruPosition::where('guru_id', $guruPosition->guru_id)
                    ->where('id', '!=', $guruPosition->id)
                    ->update([
                        'is_active' => false,
                        'tanggal_selesai' => $validated['tanggal_mulai']
                    ]);
            }

            $guruPosition->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data penugasan diperbarui!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal update data.'
            ], 500);
        }
    }


    /**
     * Hapus riwayat penugasan
     */
    public function destroy(GuruPosition $guruPosition)
    {
        $guruPosition->delete();
        return response()->json(['success' => true, 'message' => 'Riwayat jabatan dihapus!']);
    }
}
