<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    // 1. Ambil semua daftar pelajaran
    public function index()
    {
        $subjects = Subject::all();
        return response()->json([
            'success' => true,
            'data' => $subjects
        ]);
    }

    // 2. Simpan pelajaran baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:subjects,code|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $subject = Subject::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Mata pelajaran berhasil ditambahkan',
            'data' => $subject
        ], 201);
    }

    // 3. Lihat detail satu pelajaran
    public function show(Subject $subject)
    {
        return response()->json([
            'success' => true,
            'data' => $subject
        ]);
    }

    // 4. Update pelajaran
    public function update(Request $request, Subject $subject)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:20|unique:subjects,code,' . $subject->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $subject->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Mata pelajaran berhasil diperbarui',
            'data' => $subject
        ]);
    }

    // 5. Hapus pelajaran
    public function destroy(Subject $subject)
    {
        $subject->delete();
        return response()->json([
            'success' => true,
            'message' => 'Mata pelajaran berhasil dihapus'
        ]);
    }
}