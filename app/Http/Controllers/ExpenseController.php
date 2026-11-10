<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index() 
    { 
        $expenses = Expense::with(['creator', 'approver'])->get();
        return view('expenses.index', compact('expenses')); 
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
        return view('expenses.show', compact('expense')); 
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
            'status' => 'nullable|string',
        ]);

        $expense->description = $request->description;
        $expense->amount = $request->amount;
        $expense->category = $request->category;
        if ($request->has('status')) {
            $expense->status = $request->status;
            if ($request->status === 'approved') {
                $expense->approved_by = auth()->check() ? auth()->id() : 1;
            }
        }
        
        $expense->save();

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully'); 
    }

    public function destroy($id) 
    { 
        $expense = Expense::findOrFail($id);
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully'); 
    }
}
