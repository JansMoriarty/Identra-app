<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PointRule;
use Illuminate\Http\Request;

class PointRuleController extends Controller
{
    public function index()
    {
        $rules = PointRule::orderBy('priority', 'desc')->get();
        return view('pages.point-rules.index', compact('rules'));
    }

    public function store(Request $request)
    {
        // Debug: Un-comment baris di bawah ini jika ingin melihat data apa yang masuk ke server
        // dd($request->all()); 

        $data = $request->validate([
            'rule_name'          => 'required|string',
            'target_role'        => 'required|string',
            'trigger_event'      => 'required|in:CHECK_IN,CHECK_OUT',
            'condition_operator' => 'required',
            'condition_time'     => 'nullable',
            'point_modifier'     => 'required|integer',
            'is_active'          => 'nullable', // Diubah ke nullable dulu untuk dicek manual
            'priority'           => 'nullable|integer',
        ]);

        // Pastikan is_active jadi boolean sejati
        $data['is_active'] = $request->has('is_active') ? (bool)$request->is_active : true;
        $data['priority']  = $data['priority'] ?? 1;

        PointRule::create($data);

        return redirect()->back()->with('success', 'Aturan Berhasil Disimpan!');
    }

    public function update(Request $request, PointRule $pointRule)
    {
        $data = $request->validate([
            'rule_name'          => 'required|string',
            'target_role'        => 'required|string',
            'trigger_event'      => 'required|in:CHECK_IN,CHECK_OUT',
            'condition_operator' => 'required',
            'condition_time'     => 'nullable',
            'point_modifier'     => 'required|integer',
            'is_active'          => 'nullable',
        ]);

        $data['is_active'] = $request->has('is_active') ? (bool)$request->is_active : false;

        $pointRule->update($data);

        return redirect()->back()->with('success', 'Aturan Berhasil Diperbarui!');
    }

    public function destroy(PointRule $pointRule)
    {
        $pointRule->delete();
        return response()->json(['success' => true]);
    }
}
