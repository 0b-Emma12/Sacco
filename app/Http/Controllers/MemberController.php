<?php
namespace App\Http\Controllers;
use App\Models\Member;
use Illuminate\Http\Request;
class MemberController extends Controller
{
    public function index()
    {
        $members = Member::orderBy('created_at', 'desc')->paginate(15);
        return view('members.index', compact('members'));
    }

    public function create()
    {
        return view('members.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'  => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name'   => 'required|string|max:100',
            'national_id'   => 'required|string|unique:members,national_id',
            'date_of_birth' => 'required|date',
            'phone_number'  => 'required|string',
            'country'       => 'required|string',
            'county'        => 'required|string',
            'city'          => 'required|string',
            'next_of_kin'   => 'nullable|array',
        ]);

        // Ensure NOT NULL columns that are no longer in the form always have a value
        $data['middle_name'] = $data['middle_name'] ?? '';
        $data['kra_pin']     = '';

        $data['id'] = (string) \Illuminate\Support\Str::uuid();
        $data['membership_number'] = 'MBR-' . str_pad(Member::count() + 1, 3, '0', STR_PAD_LEFT);
        $data['date_joined'] = now();
        $data['status'] = 'Active';
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $member = Member::create($data);

            if (isset($data['next_of_kin'])) {
                foreach ($data['next_of_kin'] as $nok) {
                    if (!empty($nok['name'])) {
                        $member->nextOfKins()->create([
                            'name'         => $nok['name'],
                            'relationship' => $nok['relationship'] ?? '',
                            'phone_number' => $nok['phone_number'] ?? '',
                            'national_id'  => $nok['national_id'] ?? '',
                        ]);
                    }
                }
            }

            // Automatically create Default Share Capital Account
            $shareProduct = \App\Models\Product::where('name', 'like', '%Share Capital%')->first();
            if ($shareProduct) {
                $accountNumber = 'AC' . str_pad($shareProduct->id, 2, '0', STR_PAD_LEFT) . rand(100000, 999999);
                while(\App\Models\Account::where('account_number', $accountNumber)->exists()) {
                    $accountNumber = 'AC' . str_pad($shareProduct->id, 2, '0', STR_PAD_LEFT) . rand(100000, 999999);
                }

                \App\Models\Account::create([
                    'member_id' => $member->id,
                    'product_id' => $shareProduct->id,
                    'account_number' => $accountNumber,
                    'balance' => 0,
                    'is_active' => true,
                    'is_default' => true
                ]);
            }
        });

        return redirect()->route('members.index')->with('success', 'Member registered and default Share Capital account created successfully.');
    }

    public function show($id)
    {
        $member = Member::with(['employmentDetail', 'nextOfKins', 'accounts', 'loans'])->findOrFail($id);
        
        $accountNumbers = $member->accounts->pluck('account_number');
        $transactions = \App\Models\Transaction::whereIn('account_number', $accountNumbers)
            ->orderBy('created_at', 'desc')
            ->get();

        $hasShareCapital = $member->accounts()->whereHas('product', function($q) {
            $q->where('name', 'like', '%Share Capital%');
        })->exists();

        $products = \App\Models\Product::where('is_active', true)
            ->when($hasShareCapital, function($query) {
                $query->where('name', 'not like', '%Share Capital%');
            })
            ->get();

        return view('members.show', compact('member', 'transactions', 'products'));
    }

    public function edit($id)
    {
        $member = Member::with(['employmentDetail', 'nextOfKins'])->findOrFail($id);
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id);
        
        $data = $request->validate([
            'salutation' => 'nullable|string',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'national_id' => 'required|string|unique:members,national_id,'.$id,
            'phone_number' => 'required|string',
            'email' => 'nullable|email',
            'date_of_birth' => 'required|date',
            'country' => 'required|string',
            'county' => 'required|string',
            'city' => 'required|string',
            'status' => 'required|string',
            
            'employment.employment_type' => 'required|string',
            'employment.employer_name' => 'nullable|string',
            'employment.job_title' => 'nullable|string',
            'employment.monthly_income' => 'nullable|numeric',
            'employment.business_name' => 'nullable|string',
            'employment.business_type' => 'nullable|string',
            
            'next_of_kin' => 'nullable|array',
        ]);

        $member->update([
            'salutation' => $data['salutation'] ?? $member->salutation,
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? '',
            'last_name' => $data['last_name'],
            'national_id' => $data['national_id'],
            'phone_number' => $data['phone_number'],
            'email' => $data['email'] ?? null,
            'date_of_birth' => $data['date_of_birth'],
            'country' => $data['country'],
            'county' => $data['county'],
            'city' => $data['city'],
            'status' => $data['status'],
        ]);

        if (isset($data['employment'])) {
            $member->employmentDetail()->updateOrCreate(
                ['member_id' => $member->id],
                $data['employment']
            );
        }

        if (isset($data['next_of_kin'])) {
            $member->nextOfKins()->delete();
            foreach ($data['next_of_kin'] as $nok) {
                if(!empty($nok['name'])) {
                    $member->nextOfKins()->create([
                        'name'         => $nok['name'],
                        'relationship' => $nok['relationship'] ?? '',
                        'phone_number' => $nok['phone_number'] ?? '',
                        'national_id'  => $nok['national_id'] ?? '',
                    ]);
                }
            }
        }

        return redirect()->route('members.show', $member->id)->with('success', 'Member updated successfully.');
    }

    public function destroy($id)
    {
        $member = Member::findOrFail($id);
        
        // Ensure cascading deletion of related entities
        foreach ($member->accounts as $account) {
            \App\Models\Transaction::where('account_number', $account->account_number)->delete();
            $account->delete();
        }
        
        foreach ($member->loans as $loan) {
            $loan->guarantorPledges()->delete();
            $loan->repayments()->delete();
            $loan->delete();
        }
        
        // Delete pledges where this member acted as a guarantor
        $member->guarantorPledges()->delete();
        
        $member->nextOfKins()->delete();
        $member->delete();

        return redirect()->route('members.index')->with('success', 'Member deleted successfully.');
    }

    public function buyShares(Request $request, $id)
    {
        $member = Member::findOrFail($id);

        if (in_array(strtolower($member->status), ['exited', 'closed'])) {
            return redirect()->back()->withErrors(['shares_amount' => 'This member has ' . strtolower($member->status) . ' the SACCO. Share purchases are not allowed.']);
        }
        
        $request->validate([
            'shares_amount' => 'required|numeric|min:1000'
        ]);

        $amount = $request->input('shares_amount');

        // Verify member has enough liquid savings to buy the shares
        if ($member->available_savings < $amount) {
            return redirect()->back()->withErrors(['shares_amount' => 'Insufficient liquid savings to purchase these shares.'])->withInput();
        }

        $account = $member->accounts()->first();
        if ($account) {
            // Deduct from savings
            $account->balance -= $amount;
            $account->save();
            
            $member->total_savings -= $amount;
            
            // Add to Non-Withdrawable Share Capital Equity
            $member->share_capital += $amount;
            $member->save();

            \App\Models\Transaction::create([
                'reference' => 'SHR-'.\Illuminate\Support\Str::random(6),
                'account_number' => $account->account_number,
                'transaction_type' => 'share_transfer', // Internal: savings -> share capital (no cash leaves the SACCO)
                'amount' => $amount,
                'narration' => 'Purchase of Non-Withdrawable Share Capital',
                'performed_by_username' => auth()->user() ? auth()->user()->username : 'System',
            ]);
        }

        return redirect()->back()->with('success', 'Successfully purchased UGX ' . number_format($amount) . ' in shares. This equity is now locked.');
    }

    public function exitSacco($id)
    {
        $member = Member::findOrFail($id);

        if ($member->status === 'Exited') {
            return redirect()->back()->withErrors(['error' => 'Member has already exited the SACCO.']);
        }

        // Verify no active loans
        $activeLoans = $member->loans()->whereNotIn('status', ['paid', 'rejected'])->count();
        if ($activeLoans > 0) {
            return redirect()->back()->withErrors(['error' => 'Cannot exit SACCO while having active or defaulted loans.']);
        }

        // Run all steps atomically: if anything fails, nothing is saved,
        // so a retry can never create a duplicate refund.
        \Illuminate\Support\Facades\DB::transaction(function () use ($member) {
            $account = $member->accounts()->first();
            if ($account) {
                // Refund Savings + Share Capital
                $totalRefund = $member->total_savings + $member->share_capital;

                \App\Models\Transaction::create([
                    'reference' => 'EXT-'.\Illuminate\Support\Str::random(6),
                    'account_number' => $account->account_number,
                    'transaction_type' => 'withdrawal',
                    'amount' => $totalRefund,
                    'narration' => 'Full Member Exit Refund (Savings + Share Capital)',
                    'performed_by_username' => auth()->user() ? auth()->user()->username : 'System',
                ]);
            }

            // Deactivate ALL of the member's accounts so no further transactions can occur
            $member->accounts()->update(['balance' => 0, 'is_active' => false]);

            // Deactivate Member
            $member->total_savings = 0;
            $member->share_capital = 0;
            $member->status = 'Exited';
            $member->save();

            // Release any active guarantor pledges they made
            \App\Models\GuarantorPledge::where('guarantor_member_id', $member->id)
                ->where('status', 'frozen')
                ->update(['status' => 'released']);
        });

        return redirect()->back()->with('success', 'Member formally exited from the SACCO. All equity and savings refunded.');
    }
}
