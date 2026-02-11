<?php

namespace App\Http\Controllers;

// Jangan lupa import Model LeaveRequest
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestWebController extends Controller // Nama class disamakan dengan nama file
{

    public function index()
    {
        $requests = LeaveRequest::with('guru')->latest()->paginate(10);
        // Sesuaikan path view dengan folder kamu
        return view('pages.leave_request.index', compact('requests'));
    }

    // Proses Approve atau Tolak
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:disetujui,ditolak'
        ]);

        $leave = LeaveRequest::findOrFail($id);
        $leave->update([
            'status'      => $request->status,
            'approved_by' => auth()->id(), // Admin yang sedang login
        ]);

        return back()->with('success', 'Status pengajuan berhasil diperbarui.');
    }
}
