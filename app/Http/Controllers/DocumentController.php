<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::latest()->paginate(10);
        return view('documents.index', compact('documents'));
    }

    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            // Stubbed file upload logic
            // $filePath = $request->file('file')->store('documents', 'public');
            $filePath = 'dummy/path/' . $request->file('file')->getClientOriginalName();
        }

        Document::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'file_path' => $filePath,
        ]);

        return redirect()->route('documents.index')->with('success', 'Document created successfully');
    }

    public function show($id)
    {
        $document = Document::findOrFail($id);
        return view('documents.show', compact('document'));
    }

    public function edit($id)
    {
        $document = Document::findOrFail($id);
        return view('documents.edit', compact('document'));
    }

    public function update(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:10240',
        ]);

        $filePath = $document->file_path;
        if ($request->hasFile('file')) {
            // Stubbed file upload logic
            $filePath = 'dummy/path/' . $request->file('file')->getClientOriginalName();
        }

        $document->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'file_path' => $filePath,
        ]);

        return redirect()->route('documents.index')->with('success', 'Document updated successfully');
    }

    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        // If there was a real file path, we'd delete it here: Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Document deleted successfully');
    }
}
