@extends('layouts.app')

@section('title', 'Company Documents')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-5xl">
    
    <!-- PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Company Documents</h1>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 border border-indigo-600 rounded hover:bg-indigo-700 transition">
                <i data-lucide="upload" class="w-4 h-4"></i> Upload Document
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 text-emerald-700 p-4 rounded-lg text-sm border border-emerald-100">
        {{ session('success') }}
    </div>
    @endif

    <!-- DATA TABLE -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800">
        <div class="p-6 flex items-center justify-between border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center dark:bg-indigo-500/20">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Document Management</h3>
            </div>
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-slate-400"></i>
                <input type="text" placeholder="Search documents..." class="pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500/50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200">
            </div>
        </div>
        
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/50 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Title</th>
                        <th class="px-6 py-4 font-semibold">Description</th>
                        <th class="px-6 py-4 font-semibold">Date Uploaded</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($documents as $document)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                        <td class="px-6 py-4 font-medium text-slate-800 dark:text-slate-200 flex items-center gap-3">
                            <i data-lucide="file" class="w-4 h-4 text-slate-400"></i>
                            {{ $document->title }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($document->description, 50) }}</td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">{{ $document->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-right flex justify-end gap-3">
                            @if($document->file_path)
                            <a href="#" class="text-slate-400 hover:text-indigo-600" title="Download"><i data-lucide="download" class="w-4 h-4"></i></a>
                            @endif
                            <form action="{{ route('documents.destroy', $document->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this document?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="h-16 w-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mb-4 dark:bg-slate-800/50">
                                    <i data-lucide="file-x" class="w-8 h-8"></i>
                                </div>
                                <h4 class="text-slate-700 dark:text-slate-200 font-medium mb-1">No documents found</h4>
                                <p class="text-slate-500 text-sm max-w-sm mb-4">You haven't uploaded any company documents yet. Get started by uploading your first document.</p>
                                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition dark:bg-indigo-500/10 dark:hover:bg-indigo-500/20">
                                    Upload Document
                                </button>
                            </div>
                        </td>
                    </tr>
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
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('uploadModal').classList.add('hidden')"></div>
    <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md p-6 border border-slate-100 dark:border-slate-800">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Upload Document</h3>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Document Title</label>
                <input type="text" name="title" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500/50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200" placeholder="e.g. Employee Handbook">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description (Optional)</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500/50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200" placeholder="Brief description of the document..."></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">File</label>
                <input type="file" name="file" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-500/20 dark:file:text-indigo-400">
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-700">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Upload
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
