@extends('layouts.app')

@section('title', 'Company Documents')

@section('content')
<div class="mx-auto w-full max-w-6xl flex flex-col gap-6">

    @if(session('success'))
    <div class="bg-[#38CE3C]/10 text-[#38CE3C] p-4 rounded-xl text-sm font-semibold border border-[#38CE3C]/20">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-[#FF4D6B]/10 text-[#FF4D6B] p-4 rounded-xl text-sm font-semibold border border-[#FF4D6B]/20">
        {{ $errors->first() }}
    </div>
    @endif

    <x-page-header
        eyebrow="Records & Compliance"
        title="Company Documents"
        subtitle="Regulatory filings, member agreements, policies and bylaws kept on file.">
        <a href="#upload-modal" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold transition inline-flex items-center gap-2 shadow-sm">
            <x-icon name="upload" class="w-4 h-4"/>
            Upload document
        </a>
    </x-page-header>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Documents" :value="number_format($stats['total'])" meta="Records in the library" icon="folder" tone="green"/>
        <x-stat-card label="With stored file" :value="number_format($stats['with_file'])" meta="Downloadable attachments" icon="file" tone="blue"/>
        <x-stat-card label="Without file" :value="number_format($stats['without_file'])" meta="Metadata only" icon="file-x" tone="amber"/>
        <x-stat-card label="Added this month" :value="number_format($stats['this_month'])" meta="Uploaded since month start" icon="upload" tone="purple"/>
    </div>

    <!-- DATA TABLE -->
    <x-panel title="Document library" subtitle="Newest uploads first, ten per page." icon="folder">
        <x-slot name="actions">
            <form method="GET" action="{{ route('documents.index') }}" class="flex flex-wrap items-center gap-2 m-0">
                <div class="relative">
                    <x-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"/>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search documents..."
                           class="h-10 w-full sm:w-64 pl-9 pr-3 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100">
                </div>
                <button type="submit" class="h-10 px-4 rounded-xl bg-[#181824] text-white text-sm font-semibold hover:bg-slate-700 transition dark:bg-white dark:text-slate-900">Search</button>
                @if(request('q'))
                    <a href="{{ route('documents.index') }}" class="h-10 px-3 inline-flex items-center rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition dark:border-slate-700 dark:text-slate-300">Clear</a>
                @endif
            </form>
        </x-slot>

        <div class="w-full overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 dark:bg-slate-950/50">
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Title</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Description</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Date uploaded</th>
                        <th class="h-11 px-5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($documents as $document)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-xl bg-[#38CE3C]/10 text-[#38CE3C] flex items-center justify-center shrink-0">
                                    <x-icon name="file" class="w-4 h-4"/>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $document->title }}</div>
                                    <div class="text-[11px] text-slate-400">#{{ $document->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                            <p class="max-w-xs truncate" title="{{ $document->description }}">{{ \Illuminate\Support\Str::limit($document->description, 60) ?: '—' }}</p>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $document->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            @if($document->file_path)
                                <a href="{{ route('documents.download', $document->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] text-xs font-semibold hover:bg-[#38CE3C]/20 transition">Download</a>
                            @endif
                            <a href="#delete-document-{{ $document->id }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition dark:bg-slate-800 dark:text-slate-300">Delete</a>
                        </td>
                    </tr>
                    @empty
                        <x-empty-state colspan="4" icon="file-x" title="No documents found"
                                       message="Upload the first company document to start the compliance record.">
                            <a href="#upload-modal" class="h-9 px-4 rounded-lg bg-[#38CE3C] text-white text-xs font-semibold inline-flex items-center gap-2 hover:bg-[#2cb030] transition">
                                <x-icon name="upload" class="w-3.5 h-3.5"/> Upload document
                            </a>
                        </x-empty-state>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(method_exists($documents, 'hasPages') && $documents->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $documents->links() }}
        </div>
        @endif
    </x-panel>
</div>

<!-- Upload Modal (CSS :target, no JavaScript) -->
<div id="upload-modal" class="modal">
    <a href="#" class="modal-overlay" aria-label="Close"></a>
    <div class="modal-card bg-white dark:bg-[#181824] rounded-2xl shadow-xl p-6 border border-slate-200/80 dark:border-[#272738]">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Upload Document</h3>
            <a href="#" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <x-icon name="x" class="w-5 h-5"/>
            </a>
        </div>

        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Document Title</label>
                <input type="text" name="title" required value="{{ old('title') }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200" placeholder="e.g. SACCO Bylaws &amp; Constitution">
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Description (Optional)</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200" placeholder="Brief description of the document...">{{ old('description') }}</textarea>
            </div>

            <div class="mb-6">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">File</label>
                <input type="file" name="file" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#38CE3C]/10 file:text-[#38CE3C] hover:file:bg-[#38CE3C]/20">
            </div>

            <div class="flex justify-end gap-3">
                <a href="#" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-[#38CE3C] hover:bg-[#2cb030] rounded-xl shadow-xs transition">
                    Upload Document
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete confirmations (CSS :target, no JavaScript) -->
@foreach($documents as $document)
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
            <a href="#" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">Cancel</a>
            <form action="{{ route('documents.destroy', $document->id) }}" method="POST" class="m-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-[#FF4D6B] hover:bg-[#e63c58] rounded-xl transition">Delete</button>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
