@extends('layouts.app')
@section('title', 'Document Details')
@section('content')
<div class="mx-auto w-full max-w-6xl flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div class="flex items-start gap-3">
            <a href="{{ route('documents.index') }}" class="mt-1 p-2 bg-white rounded-xl border border-slate-200 text-slate-500 hover:text-[#38CE3C] hover:border-[#38CE3C]/30 hover:bg-[#38CE3C]/5 transition dark:bg-[#181824] dark:border-[#272738]" title="Back to documents">
                <x-icon name="arrow-left" class="w-4 h-4"/>
            </a>
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Company document</div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">{{ $document->title }}</h1>
                <p class="text-sm text-slate-500 mt-1">Uploaded {{ $document->created_at ? $document->created_at->format('d M Y') : '—' }} &middot; Record #{{ $document->id }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('documents.index') }}" class="h-10 px-4 inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200">
                <x-icon name="folder" class="w-4 h-4"/> Document library
            </a>
            @if($document->file_path)
                <a href="{{ route('documents.download', $document->id) }}" class="h-10 px-4 inline-flex items-center gap-2 rounded-xl bg-[#38CE3C] text-white text-sm font-semibold shadow-sm hover:bg-[#2cb030] transition">
                    <x-icon name="download" class="w-4 h-4"/> Download file
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 flex flex-col gap-6">
            <x-panel title="Document particulars" subtitle="Record details held in the company library." icon="file-text">
                <dl class="grid gap-x-6 gap-y-5 p-5 sm:p-6 sm:grid-cols-2 text-sm">
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Title</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-100">{{ $document->title }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Record ID</dt>
                        <dd class="font-semibold text-slate-800 dark:text-slate-100">#{{ $document->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Date uploaded</dt>
                        <dd class="text-slate-800 dark:text-slate-100">{{ $document->created_at ? $document->created_at->format('d M Y, H:i') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Last updated</dt>
                        <dd class="text-slate-800 dark:text-slate-100">{{ $document->updated_at ? $document->updated_at->format('d M Y, H:i') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Stored file</dt>
                        <dd>
                            @if($document->file_path)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border bg-[#38CE3C]/15 text-[#38CE3C] border-[#38CE3C]/20">Attached</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">No file attached</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">File path</dt>
                        <dd class="text-slate-800 dark:text-slate-100 break-all">{{ $document->file_path ?: '—' }}</dd>
                    </div>
                </dl>
            </x-panel>

            <x-panel title="Description" subtitle="Notes recorded when the document was filed." icon="file" tone="slate">
                <div class="p-5 sm:p-6">
                    <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $document->description ?: 'No description was recorded for this document.' }}</p>
                </div>
            </x-panel>
        </div>

        <div class="flex flex-col gap-6">
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 dark:bg-[#181824] dark:border-[#272738]">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">Stored file</span>
                    <span class="p-2 rounded-xl {{ $document->file_path ? 'bg-[#38CE3C]/10 text-[#38CE3C]' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300' }}">
                        <x-icon :name="$document->file_path ? 'file-text' : 'file-x'" class="w-4 h-4"/>
                    </span>
                </div>
                <div class="mt-3 text-lg font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ $document->file_path ? 'Available' : 'Not attached' }}
                </div>
                <p class="text-[11px] font-medium text-slate-500 mt-1">{{ $document->file_path ? 'Ready to download from secure storage' : 'This record holds metadata only' }}</p>
            </div>

            <x-panel title="Document actions" subtitle="Download, remove or return to the library." icon="folder" tone="purple">
                <div class="p-5 space-y-2">
                    @if($document->file_path)
                        <a href="{{ route('documents.download', $document->id) }}" class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                            <span class="flex items-center gap-2.5 text-sm font-medium text-slate-700 dark:text-slate-200">
                                <x-icon name="download" class="w-4 h-4 text-[#38CE3C]"/> Download file
                            </span>
                            <x-icon name="arrow-right" class="w-4 h-4 text-slate-400"/>
                        </a>
                    @endif
                    <a href="{{ route('documents.index') }}" class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                        <span class="flex items-center gap-2.5 text-sm font-medium text-slate-700 dark:text-slate-200">
                            <x-icon name="folder" class="w-4 h-4 text-[#8E32E9]"/> Document library
                        </span>
                        <x-icon name="arrow-right" class="w-4 h-4 text-slate-400"/>
                    </a>
                    <a href="#delete-document-{{ $document->id }}" class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition dark:bg-slate-900/50 dark:hover:bg-slate-800/50">
                        <span class="flex items-center gap-2.5 text-sm font-medium text-slate-700 dark:text-slate-200">
                            <x-icon name="trash-2" class="w-4 h-4 text-[#FF4D6B]"/> Delete document
                        </span>
                        <x-icon name="arrow-right" class="w-4 h-4 text-slate-400"/>
                    </a>
                </div>
            </x-panel>
        </div>
    </div>
</div>

<!-- Delete confirmation (CSS :target, no JavaScript) -->
<div id="delete-document-{{ $document->id }}" class="modal">
    <a href="#" class="modal-overlay" aria-label="Close"></a>
    <div class="modal-card bg-white dark:bg-[#181824] rounded-2xl shadow-xl p-6 border border-slate-200/80 dark:border-[#272738] max-w-sm">
        <div class="flex items-center gap-3 mb-4">
            <span class="h-10 w-10 rounded-xl bg-[#FF4D6B]/10 text-[#FF4D6B] flex items-center justify-center">
                <x-icon name="alert-triangle" class="w-5 h-5"/>
            </span>
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Delete document?</h3>
                <p class="text-xs text-slate-500">{{ $document->title }}</p>
            </div>
        </div>
        <p class="text-xs text-slate-500 mb-6">This permanently removes the record and its stored file.</p>
        <div class="flex justify-end gap-3">
            <a href="#" class="h-10 px-4 inline-flex items-center rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">Cancel</a>
            <form action="{{ route('documents.destroy', $document->id) }}" method="POST" class="m-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="h-10 px-4 rounded-xl bg-[#FF4D6B] text-white text-sm font-semibold hover:bg-[#e63c58] transition">Delete</button>
            </form>
        </div>
    </div>
</div>
@endsection
