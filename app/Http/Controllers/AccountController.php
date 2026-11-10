<?php
namespace App\Http\Controllers;
use App\Models\Account;
use Illuminate\Http\Request;
class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::with(['member', 'product'])->orderBy('created_at', 'desc')->paginate(15);
        return view('accounts.index', compact('accounts'));
    }

    public function create() { return view('accounts.create'); }
    public function store(Request $request) {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'product_id' => 'required|exists:products,id',
            'term_months' => 'nullable|integer',
            'interest_rate' => 'nullable|numeric',
        ]);

        $product = \App\Models\Product::findOrFail($validated['product_id']);
        if (str_contains(strtolower($product->name), 'fixed')) {
            $request->validate([
                'term_months' => 'required|integer'
            ]);
            $validated['maturity_date'] = now()->addMonths($request->term_months)->toDateString();
        }

        // Limit 1 per product type
        $existing = Account::where('member_id', $validated['member_id'])
            ->where('product_id', $validated['product_id'])
            ->exists();
        if ($existing) {
            return redirect()->back()->with('error', 'Member already has an account of this product type.');
        }

        // Default account logic
        $accountsCount = Account::where('member_id', $validated['member_id'])->count();
        $validated['is_default'] = ($accountsCount === 0);

        // Generate a random 10-digit account number (or sequential)
        // Here we do a simple prefix based on product ID + random digits
        $accountNumber = 'AC' . str_pad($validated['product_id'], 2, '0', STR_PAD_LEFT) . rand(100000, 999999);

        // Ensure uniqueness (extremely unlikely to collide, but good practice)
        while(Account::where('account_number', $accountNumber)->exists()) {
            $accountNumber = 'AC' . str_pad($validated['product_id'], 2, '0', STR_PAD_LEFT) . rand(100000, 999999);
        }

        $validated['account_number'] = $accountNumber;
        $validated['balance'] = 0;
        $validated['is_active'] = true;

        Account::create($validated);

        return redirect()->back()->with('success', "Account {$accountNumber} created successfully."); 
    }
    
    public function show($id) { 
        $account = Account::with(['member', 'product'])->findOrFail($id);
        $transactions = \App\Models\Transaction::where('account_number', $account->account_number)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('accounts.show', compact('account', 'transactions')); 
    }
    
    public function edit($id) { return view('accounts.edit', compact('id')); }
    public function update(Request $request, $id) { return redirect()->route('accounts.index')->with('success', 'Updated successfully'); }
    public function destroy($id) { 
        $account = Account::findOrFail($id);
        
        // Cascading deletion for related transactions
        \App\Models\Transaction::where('account_number', $account->account_number)->delete();
        
        $account->delete();

        return redirect()->back()->with('success', 'Account deleted successfully'); 
    }
}
