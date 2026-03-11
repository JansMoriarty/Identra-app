<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssessmentCategoryController extends Controller
{
    // 1. Tampilkan semua kategori
    public function index()
    {
        $categories = AssessmentCategory::all();
        return response()->json([
            'success' => true,
            'data' => $categories
        ], 200);
    }

    // 2. Simpan kategori baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $category = AssessmentCategory::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dibuat',
            'data' => $category
        ], 201);
    }

    // 3. Update kategori
    public function update(Request $request, $id)
    {
        $category = AssessmentCategory::find($id);

        if (!$category) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }

        $category->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diupdate',
            'data' => $category
        ], 200);
    }

    // 4. Hapus kategori
    public function destroy($id)
    {
        $category = AssessmentCategory::find($id);

        if (!$category) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus'
        ], 200);
    }
}