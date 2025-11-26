<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Admin/Dashboard');
    }

    public function index()
    {
        return Inertia::render('Admin/Documents/Index', [
            'documents' => Document::orderBy('id', 'asc')->paginate(20)
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Documents/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'text'  => 'nullable'
        ]);

        Document::create($request->only('title', 'text'));

        return redirect()->route('documents.index');
    }

    public function edit(Document $document)
    {
        return Inertia::render('Admin/Documents/Edit', [
            'document' => $document
        ]);
    }

    public function update(Request $request, Document $document)
    {
        $request->validate([
            'title' => 'required',
            'text'  => 'nullable'
        ]);

        $document->update($request->only('title', 'text'));

        return redirect()->route('documents.index');
    }

    public function destroy(Document $document)
    {
        $document->delete();
        return redirect()->route('documents.index');
    }
}
