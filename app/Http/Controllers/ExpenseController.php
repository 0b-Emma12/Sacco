<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\JournalEntry;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseController extends Controller
{
    public function index(Request $request) 
    { 
        $query = Expense::with(['creator', 'approver']);

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $summary = [
            'count' => (clone $query)->count(),
            'total' => (float) (clone $query)->sum('amount'),
            'approved' => (float) (clone $query)->where('status', 'approved')->sum('amount'),
            'pending' => (clone $query)->where('status', 'pending')->count(),
        ];

        $expenses = $query->orderByDesc('date')->orderByDesc('id')->paginate(15)->withQueryString();
        return view('expenses.index', compact('expenses', 'summary')); 
    }

    public function create() 
    { 
        return view('expenses.create'); 
    }

    public function store(Request $request) 
    { 
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string',
            'date' => 'nullable|date'
        ]);

        $expense = new Expense();
        $expense->description = $request->description;
        $expense->amount = $request->amount;
        $expense->category = $request->category;
        $expense->date = $request->date ?? now();
        $expense->created_by = auth()->check() ? auth()->id() : 1; 
        $expense->status = 'pending';
        $expense->save();

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully'); 
    }

    public function show($id) 
    { 
        $expense = Expense::with(['creator', 'approver'])->findOrFail($id);
        $journalEntry = JournalEntry::with('lineItems')
            ->where('reference_type', 'expense')
            ->where('reference_id', $expense->id)
            ->latest('id')
            ->first();
        return view('expenses.show', compact('expense', 'journalEntry')); 
    }

    public function edit($id) 
    { 
        $expense = Expense::findOrFail($id);
        return view('expenses.edit', compact('expense')); 
    }

    public function update(Request $request, $id) 
    { 
        $expense = Expense::findOrFail($id);

        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string',
            'status' => 'nullable|in:pending,approved,rejected',
        ]);

        if ($expense->status === 'approved') {
            throw ValidationException::withMessages([
                'expense' => 'This expense is already approved and posted. Reverse the ledger entry before editing it.',
            ]);
        }

        $status = $request->input('status', $expense->status);

        if ($status === 'approved') {
            $user = $request->user();
            if (! $user || ! $user->canApproveLoans()) {
                throw ValidationException::withMessages([
                    'expense' => 'Only the Administrator or Manager may approve an expense.',
                ]);
            }
        }

        $expense->description = $request->description;
        $expense->amount = $request->amount;
        $expense->category = $request->category;
        $expense->status = $status;
        if ($status === 'approved') {
            $expense->approved_by = $request->user()->id;
        }

        DB::transaction(function () use ($expense, $status) {
            $expense->save();

            if ($status !== 'approved') {
                return;
            }

            $alreadyPosted = JournalEntry::where('reference_type', 'expense')
                ->where('reference_id', $expense->id)
                ->exists();

            if ($alreadyPosted) {
                return;
            }

            LedgerService::post(
                'EXP',
                'Expense #'.str_pad($expense->id, 4, '0', STR_PAD_LEFT).' — '.$expense->category,
                [
                    ['code' => '5000', 'debit' => round((float) $expense->amount, 2)],
                    ['code' => '1000', 'credit' => round((float) $expense->amount, 2)],
                ],
                'expense',
                $expense->id
            );
        });

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully'); 
    }

    public function destroy($id) 
    { 
        $expense = Expense::findOrFail($id);

        if ($expense->status === 'approved') {
            return redirect()->route('expenses.show', $expense->id)
                ->withErrors(['expense' => 'Approved expenses cannot be deleted because they are posted to the ledger.']);
        }

        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully'); 
    }
}
