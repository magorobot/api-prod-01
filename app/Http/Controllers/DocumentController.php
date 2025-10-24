<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function index()
    {
        $household = auth()->user()->household;

        $documents = Document::where('household_id', $household->id)
            ->with('uploader')
            ->latest('created_at')
            ->get();

        return Inertia::render('House/Documents', [
            'documents' => $documents,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,gif|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        Document::create([
            'household_id' => auth()->user()->household_id,
            'title' => $validated['title'],
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->route('documents.index')->with('success', 'Documento caricato con successo.');
    }

    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        // Elimina il file dallo storage
        Storage::disk('public')->delete($document->file_path);

        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Documento eliminato con successo.');
    }

    public function download(Document $document)
    {
        $this->authorize('view', $document);

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }
}
