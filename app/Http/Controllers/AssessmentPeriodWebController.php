<?php

namespace App\Http\Controllers;

use App\Models\AssessmentPeriod;
use Illuminate\Http\Request;

class AssessmentPeriodWebController extends Controller
{
    public function index()
    {
        $periods = AssessmentPeriod::orderBy('start_date', 'desc')->get();
        return view('pages.assessment-periods.index', compact('periods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        AssessmentPeriod::create($request->all());
        return redirect()->back()->with('success', 'Periode berhasil ditambah!');
    }

    public function update(Request $request, $id)
    {
        $period = AssessmentPeriod::findOrFail($id);
        $period->update($request->all());
        return redirect()->back()->with('success', 'Periode berhasil diupdate!');
    }

    public function destroy($id)
    {
        AssessmentPeriod::destroy($id);
        return response()->json(['status' => 'success']);
    }
}