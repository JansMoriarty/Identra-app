<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AssessmentCategory;
use Illuminate\Http\Request;

class AssessmentCategoryWebController extends Controller
{
    /**
     * Menampilkan daftar kategori
     */
    public function index()
    {
        $categories = AssessmentCategory::all();
        return view('pages.assessment-categories.index', [
            'title' => 'Kategori Penilaian',
            'categories' => $categories
        ]);
    }

    /**
     * Menyimpan kategori baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|integer'
        ]);

        AssessmentCategory::create($request->all());

        return redirect()->back()->with('success', 'Kategori penilaian berhasil ditambahkan!');
    }

    /**
     * Menghapus kategori (via AJAX/Alpine)
     */
    public function destroy($id)
    {
        $category = AssessmentCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Kategori berhasil dihapus'
        ]);
    }
}