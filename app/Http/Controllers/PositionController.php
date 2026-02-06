<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::latest()->get();
        return view('pages.positions.index', compact('positions'));
    }

    public function create()
    {
        return view('pages.positions.create', [
            'title' => 'Tambah Jabatan Baru'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255',
            'keterangan'   => 'nullable|string',
        ]);

        // Auto Generate Kode Jabatan
        $today = now()->format('Ymd');
        $lastPosition = Position::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastPosition && preg_match('/-(\d+)$/', $lastPosition->kode_jabatan, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1;
        }

        $validated['kode_jabatan'] = 'POS-' . $today . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);

        Position::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jabatan berhasil dibuat!'
            ]);
        }

        return redirect()->route('positions.index')->with('success', 'Jabatan berhasil ditambahkan!');
    }

    /**
     * Tampilkan form edit (PENTING: Jangan sampai lewat)
     */
    public function edit(Position $position)
    {
        return view('pages.positions.edit', [
            'title'    => 'Edit Jabatan',
            'position' => $position
        ]);
    }

    /**
     * Proses update data
     */
    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255',
            'keterangan'   => 'nullable|string', // Tetap simpan keterangan jika ada formnya
            // 'kode_jabatan' biasanya tidak diupdate karena bersifat unik/history
        ]);

        $position->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jabatan berhasil diperbarui!'
            ]);
        }

        return redirect()->route('positions.index')->with('success', 'Jabatan berhasil diperbarui!');
    }

    public function destroy(Position $position)
    {
        $position->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Jabatan berhasil dihapus!');
    }
}