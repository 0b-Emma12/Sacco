<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::latest();

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $documents = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => Document::count(),
            'with_file' => Document::whereNotNull('file_path')->count(),
            'without_file' => Document::whereNull('file_path')->count(),
            'this_month' => Document::where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        return view('documents.index', compact('documents', 'stats'));
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
            $filePath = $request->file('file')->store('documents', 'public');
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
            if ($document->file_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
            }
            $filePath = $request->file('file')->store('documents', 'public');
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

        if ($document->file_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Document deleted successfully');
    }

    public function download($id)
    {
        $document = Document::findOrFail($id);
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        abort_unless($document->file_path && $disk->exists($document->file_path), 404, 'The stored file for this document is unavailable.');

        $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
        $name = \Illuminate\Support\Str::slug($document->title) ?: 'document';

        return $disk->download($document->file_path, $name . ($extension ? '.' . $extension : ''));
    }
}
