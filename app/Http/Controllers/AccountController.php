<?php
namespace App\Http\Controllers;
use App\Models\Account;
use Illuminate\Http\Request;
class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = Account::with(['member', 'product']);

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhereHas('member', function ($m) use ($search) {
                      $m->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('membership_number', 'like', "%{$search}%");
                  });
            });
        }

        $summary = [
            'total' => (clone $query)->count(),
            'balance' => (float) (clone $query)->sum('balance'),
            'active' => (clone $query)->where('is_active', true)->count(),
            'inactive' => (clone $query)->where('is_active', false)->count(),
        ];

        $accounts = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        return view('accounts.index', compact('accounts', 'summary'));
    }

    public function create(Request $request)
    {
        $members = \App\Models\Member::orderBy('first_name')->orderBy('last_name')->get();
        $selectedMemberId = $request->input('member_id', old('member_id'));

        $products = \App\Models\Product::where('is_active', true)->orderBy('name')->get();

        if ($selectedMemberId && ($member = \App\Models\Member::find($selectedMemberId))) {
            $products = $products->reject(function ($product) use ($member) {
                if ($member->accounts()->where('product_id', $product->id)->exists()) {
                    return true;
                }

                return str_contains(strtolower($product->name), 'share capital')
                    && $member->accounts()->whereHas('product', function ($query) {
                        $query->where('name', 'like', '%Share Capital%');
                    })->exists();
            })->values();
        }

        return view('accounts.create', compact('members', 'products', 'selectedMemberId'));
    }
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
            return redirect()->route('accounts.create', ['member_id' => $validated['member_id']])
                ->withErrors(['product_id' => 'This member already has an account of this product type.'])
                ->withInput();
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

        return redirect()->route('members.show', $validated['member_id'])
            ->with('success', "Account {$accountNumber} created successfully.");
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

        $hasTransactions = \App\Models\Transaction::where('account_number', $account->account_number)->exists();
        if ($hasTransactions || (float) $account->balance != 0.0) {
            return redirect()->back()->withErrors(['error' => 'Accounts with transactions or a non-zero balance cannot be deleted. Deactivate the account instead.']);
        }

        $account->delete();

        return redirect()->back()->with('success', 'Account deleted successfully'); 
    }
}
