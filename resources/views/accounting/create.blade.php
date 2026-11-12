@extends('layouts.app')

@section('title', 'Create Journal Entry')

@section('content')
@php
    $oldLines = old('lines', []);
    $lineCount = count($oldLines) ?: max(2, (int) request('lines', 2));
    $lineCount = min(max(2, $lineCount), 20);
    $totalDebit = collect($oldLines)->sum(fn ($line) => (float) ($line['debit'] ?? 0));
    $totalCredit = collect($oldLines)->sum(fn ($line) => (float) ($line['credit'] ?? 0));
@endphp
<div class="mx-auto w-full max-w-4xl flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">New Journal Entry</h1>
            <p class="text-sm text-slate-500 mt-1">Record a manual double-entry transaction.</p>
        </div>
        <a href="{{ route('accounting.index') }}" class="flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition font-medium dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
            <x-icon name="arrow-left" class="w-4 h-4"/>
            Back to Ledger
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 mb-4 text-sm text-[#FF4D6B] bg-[#FF4D6B]/10 border border-[#FF4D6B]/20 rounded-xl font-semibold" role="alert">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800 p-6">
        <form action="{{ route('accounting.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Reference</label>
                    <input type="text" name="reference" value="{{ old('reference') }}" required class="w-full px-3 py-2 border border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] sm:text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Date</label>
                    <input type="datetime-local" name="posted_at" value="{{ old('posted_at', now()->format('Y-m-d\TH:i')) }}" required class="w-full px-3 py-2 border border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] sm:text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white transition">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Description</label>
                    <textarea name="description" rows="2" required class="w-full px-3 py-2 border border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] sm:text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white transition">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200">Line Items</h3>
                    <span class="text-xs text-slate-400">Debits must equal credits</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                                <th class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase">Account</th>
                                <th class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase">Debit</th>
                                <th class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase">Credit</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < $lineCount; $i++)
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="px-4 py-2">
                                        <select name="lines[{{ $i }}][account_code]" required class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition">
                                            <option value="">Select Account...</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->code }}" {{ old("lines.{$i}.account_code") == $account->code ? 'selected' : '' }}>
                                                    {{ $account->code }} - {{ $account->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" name="lines[{{ $i }}][debit]" value="{{ old("lines.{$i}.debit", 0) }}" min="0" step="0.01" class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" name="lines[{{ $i }}][credit]" value="{{ old("lines.{$i}.credit", 0) }}" min="0" step="0.01" class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition">
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        @if($lineCount > 2)
                                        <a href="{{ route('accounting.create', ['lines' => $lineCount - 1]) }}" class="text-slate-400 hover:text-[#FF4D6B] transition inline-flex" title="Remove this line">
                                            <x-icon name="trash-2" class="w-4 h-4"/>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 dark:bg-slate-800 font-medium">
                                <td class="px-4 py-3 text-right text-sm">Posted totals</td>
                                <td class="px-4 py-3 text-sm">{{ number_format($totalDebit, 2) }}</td>
                                <td class="px-4 py-3 text-sm">{{ number_format($totalCredit, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @if($lineCount < 20)
                <a href="{{ route('accounting.create', ['lines' => $lineCount + 1]) }}" class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-[#8E32E9] hover:text-[#7822d6] dark:text-[#8E32E9] transition">
                    <x-icon name="plus" class="w-4 h-4"/> Add Line Item
                </a>
                @endif
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('accounting.index') }}" class="px-5 py-2.5 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 transition dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-[#38CE3C] hover:bg-[#2cb030] shadow-md shadow-[#38CE3C]/20 transition">
                    Save Journal Entry
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
