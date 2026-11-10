@extends('layouts.app')

@section('title', 'Create Journal Entry')

@section('content')
<div class="mx-auto w-full max-w-4xl flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">New Journal Entry</h1>
            <p class="text-sm text-slate-500 mt-1">Record a manual double-entry transaction.</p>
        </div>
        <a href="{{ route('accounting.index') }}" class="flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition font-medium dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back to Ledger
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800 p-6">
        <form action="{{ route('accounting.store') }}" method="POST" id="journalForm">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Reference</label>
                    <input type="text" name="reference" value="{{ old('reference') }}" required class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Date</label>
                    <input type="datetime-local" name="posted_at" value="{{ old('posted_at', now()->format('Y-m-d\TH:i')) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Description</label>
                    <textarea name="description" rows="2" required class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200 mb-4">Line Items</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="linesTable">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                                <th class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase">Account</th>
                                <th class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase">Debit</th>
                                <th class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase">Credit</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody id="linesBody">
                            <!-- We start with two lines -->
                            @for ($i = 0; $i < max(2, count(old('lines', [1,2]))); $i++)
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="px-4 py-2">
                                        <select name="lines[{{ $i }}][account_code]" required class="w-full px-2 py-1 border border-slate-300 rounded-md text-sm dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                                            <option value="">Select Account...</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->code }}" {{ old("lines.{$i}.account_code") == $account->code ? 'selected' : '' }}>
                                                    {{ $account->code }} - {{ $account->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" name="lines[{{ $i }}][debit]" value="{{ old("lines.{$i}.debit", 0) }}" min="0" step="0.01" class="w-full px-2 py-1 border border-slate-300 rounded-md text-sm debit-input dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" name="lines[{{ $i }}][credit]" value="{{ old("lines.{$i}.credit", 0) }}" min="0" step="0.01" class="w-full px-2 py-1 border border-slate-300 rounded-md text-sm credit-input dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <button type="button" class="text-red-500 hover:text-red-700 remove-line"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 dark:bg-slate-800 font-medium">
                                <td class="px-4 py-3 text-right text-sm">Total</td>
                                <td class="px-4 py-3 text-sm" id="totalDebit">0.00</td>
                                <td class="px-4 py-3 text-sm" id="totalCredit">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <button type="button" id="addLine" class="mt-4 flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                    <i data-lucide="plus" class="w-4 h-4"></i> Add Line Item
                </button>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('accounting.index') }}" class="px-4 py-2 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-sacco hover:bg-sacco-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sacco">
                    Save Journal Entry
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const linesBody = document.getElementById('linesBody');
        const addLineBtn = document.getElementById('addLine');
        let lineIndex = {{ max(2, count(old('lines', [1,2]))) }};

        function calculateTotals() {
            let totalDebit = 0;
            let totalCredit = 0;
            
            document.querySelectorAll('.debit-input').forEach(input => {
                totalDebit += parseFloat(input.value) || 0;
            });
            document.querySelectorAll('.credit-input').forEach(input => {
                totalCredit += parseFloat(input.value) || 0;
            });
            
            document.getElementById('totalDebit').textContent = totalDebit.toFixed(2);
            document.getElementById('totalCredit').textContent = totalCredit.toFixed(2);
        }

        // Attach event listeners to existing inputs
        linesBody.addEventListener('input', calculateTotals);
        linesBody.addEventListener('click', function(e) {
            if (e.target.closest('.remove-line')) {
                if (linesBody.querySelectorAll('tr').length > 2) {
                    e.target.closest('tr').remove();
                    calculateTotals();
                } else {
                    alert('You must have at least two line items.');
                }
            }
        });

        addLineBtn.addEventListener('click', function() {
            const firstRow = linesBody.querySelector('tr');
            const newRow = firstRow.cloneNode(true);
            
            // Update names
            newRow.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/\[\d+\]/, `[${lineIndex}]`);
            });
            
            // Reset values
            newRow.querySelector('select').value = '';
            newRow.querySelectorAll('input').forEach(el => el.value = '0');
            
            linesBody.appendChild(newRow);
            lineIndex++;
            lucide.createIcons();
            calculateTotals();
        });

        calculateTotals();
    });
</script>
@endsection
