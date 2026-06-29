<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvaluationCriteria;
use Illuminate\Http\Request;

class CriteriaController extends Controller
{
    public function index()
    {
        $criteria = EvaluationCriteria::orderBy('name')->get();
        return view('admin.criteria.index', compact('criteria'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'weight' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:benefit,cost',
            'min_value' => 'required|integer',
            'max_value' => 'required|integer',
        ]);

        EvaluationCriteria::create($validated);

        return redirect()->route('admin.criteria.index')->with('success', 'Criteria created successfully.');
    }

    public function update(Request $request, EvaluationCriteria $criterium)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'weight' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:benefit,cost',
            'min_value' => 'required|integer',
            'max_value' => 'required|integer',
            'is_active' => 'required|boolean',
        ]);

        $criterium->update($validated);

        return redirect()->route('admin.criteria.index')->with('success', 'Criteria updated successfully.');
    }

    public function destroy(EvaluationCriteria $criterium)
    {
        $criterium->delete();
        return redirect()->route('admin.criteria.index')->with('success', 'Criteria deleted successfully.');
    }
}
