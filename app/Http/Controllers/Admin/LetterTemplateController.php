<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LetterTemplate;
use Illuminate\Http\Request;

class LetterTemplateController extends Controller
{
    public function index()
    {
        $templates = LetterTemplate::orderBy('type')->get();
        return view('admin.letter-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.letter-templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:150',
            'type'      => 'required|in:acceptance,rejection,interview_invitation',
            'subject'   => 'required|string|max:255',
            'body'      => 'required|string',
            'is_active' => 'required|boolean',
        ]);

        LetterTemplate::create($validated);

        return redirect()->route('admin.letter-templates.index')
            ->with('success', 'Template surat berhasil dibuat.');
    }

    public function edit(LetterTemplate $letterTemplate)
    {
        return view('admin.letter-templates.edit', compact('letterTemplate'));
    }

    public function update(Request $request, LetterTemplate $letterTemplate)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:150',
            'type'      => 'required|in:acceptance,rejection,interview_invitation',
            'subject'   => 'required|string|max:255',
            'body'      => 'required|string',
            'is_active' => 'required|boolean',
        ]);

        $letterTemplate->update($validated);

        return redirect()->route('admin.letter-templates.index')
            ->with('success', 'Template surat berhasil diperbarui.');
    }

    public function destroy(LetterTemplate $letterTemplate)
    {
        $letterTemplate->delete();

        return redirect()->route('admin.letter-templates.index')
            ->with('success', 'Template surat berhasil dihapus.');
    }
}
