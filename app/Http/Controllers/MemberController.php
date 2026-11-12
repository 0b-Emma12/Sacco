<?php
namespace App\Http\Controllers;
use App\Models\Member;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = Member::query();

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('membership_number', 'like', "%{$search}%");
            });
        }

        $summary = [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->whereRaw('LOWER(status) = ?', ['active'])->count(),
            'savings' => (float) (clone $query)->sum('total_savings'),
            'shares' => (float) (clone $query)->sum('share_capital'),
        ];

        $members = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        return view('members.index', compact('members', 'summary'));
    }

    public function export(Request $request)
    {
        $query = Member::orderBy('created_at', 'desc');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('membership_number', 'like', "%{$search}%");
            });
        }

        $members = $query->get();
        $filename = 'members-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($members) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Membership No', 'First Name', 'Middle Name', 'Last Name', 'National ID', 'Phone', 'Status', 'Date Joined']);
            foreach ($members as $member) {
                fputcsv($out, [
                    $member->membership_number,
                    $member->first_name,
                    $member->middle_name,
                    $member->last_name,
                    $member->national_id,
                    $member->phone_number,
                    $member->status,
                    optional($member->date_joined)->format('Y-m-d'),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
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
            'gender' => 'required|string|in:MALE,FEMALE,OTHER',
            'national_id'   => 'required|string|unique:members,national_id',
            'date_of_birth' => 'required|date',
            'phone_number'  => 'required|string',
            'country'       => 'required|string',
            'county'        => 'required|string',
            'city'          => 'required|string',
            'common_bond_type' => 'nullable|string|in:EMPLOYER,PROFESSION,COMMUNITY,CHAMA',
            'common_bond_value' => 'nullable|string|max:150',
            'next_of_kin'   => 'nullable|array',
        ]);

        // Ensure NOT NULL columns that are no longer in the form always have a value
        $data['middle_name'] = $data['middle_name'] ?? '';
        $data['kra_pin']     = '';
        $data['gender'] = $data['gender'] ?? 'OTHER';
        $data['common_bond_type'] = $data['common_bond_type'] ?? 'COMMUNITY';

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

            \App\Services\MemberCapitalService::refresh($member);
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
            'gender' => 'nullable|string|in:MALE,FEMALE,OTHER',
            'common_bond_type' => 'nullable|string|in:EMPLOYER,PROFESSION,COMMUNITY,CHAMA',
            'common_bond_value' => 'nullable|string|max:150',
            
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
            'gender' => $data['gender'] ?? $member->gender,
            'common_bond_type' => $data['common_bond_type'] ?? $member->common_bond_type,
            'common_bond_value' => $data['common_bond_value'] ?? $member->common_bond_value,
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

        $accountNumbers = $member->accounts()->pluck('account_number');
        $hasTransactions = \App\Models\Transaction::whereIn('account_number', $accountNumbers)->exists();
        $hasBalance = $member->accounts()->where('balance', '!=', 0)->exists();
        $hasLoans = $member->loans()->exists() || $member->guarantorPledges()->exists();

        if ($hasTransactions || $hasBalance || $hasLoans) {
            return redirect()->back()->withErrors(['error' => 'This member has financial history. Members are never deleted; use the Exit process to close the membership.']);
        }

        $member->accounts()->delete();
        $member->nextOfKins()->delete();
        $member->delete();

        return redirect()->route('members.index')->with('success', 'Member record deleted.');
    }

    public function verifyKyc(Request $request, $id)
    {
        $user = $request->user();
        if (! ($user && $user->canVerifyKyc())) {
            abort(403, 'Only an administrator, manager or operations manager may verify KYC.');
        }

        $member = Member::findOrFail($id);

        $data = $request->validate([
            'kyc_status' => 'required|in:VERIFIED,REJECTED,ADDITIONAL_INFO',
            'documents' => 'nullable|array',
            'documents.*' => 'in:1',
            'verified_by' => 'required|string|max:100',
            'rejection_reason' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $checklist = ['national_id', 'passport_photo', 'proof_of_address', 'proof_of_common_bond', 'next_of_kin_id'];
        $documents = [];
        foreach ($checklist as $document) {
            $documents[$document] = (bool) ($data['documents'][$document] ?? false);
        }

        $verified = $data['kyc_status'] === 'VERIFIED';

        if ($verified) {
            $missing = array_keys(array_filter($documents, fn ($onFile) => ! $onFile));
            if ($missing) {
                throw ValidationException::withMessages([
                    'documents' => 'All mandatory documents must be verified before KYC can be approved. Missing: '.implode(', ', $missing).'.',
                ]);
            }
        } elseif (blank($data['rejection_reason'] ?? null)) {
            throw ValidationException::withMessages([
                'rejection_reason' => 'State the reason for rejection or the information still required.',
            ]);
        }

        $reviewYears = max(1, (int) Setting::get('kyc_review_years', 2));

        $member->update([
            'kyc_status' => $data['kyc_status'],
            'kyc_documents' => $documents,
            'kyc_verified' => $verified,
            'kyc_verified_at' => $verified ? now() : null,
            'kyc_verified_by' => $data['verified_by'],
            'kyc_next_review_at' => $verified ? now()->addYears($reviewYears)->toDateString() : null,
            'kyc_rejection_reason' => $verified ? null : $data['rejection_reason'],
            'kyc_notes' => $data['notes'] ?? null,
        ]);

        $message = $verified
            ? 'KYC verified. Next review due '.$member->kyc_next_review_at->format('d M Y').'.'
            : 'KYC recorded as '.str_replace('_', ' ', strtolower($data['kyc_status'])).'.';

        return redirect()->back()->with('success', $message);
    }

    public function buyShares(Request $request, $id)
    {
        $member = Member::findOrFail($id);

        if (in_array(strtolower($member->status), ['exited', 'closed', 'deceased'], true)) {
            return redirect()->back()->withErrors(['shares_amount' => 'This member has left the SACCO. Share purchases are not allowed.']);
        }
        
        $request->validate([
            'shares_amount' => 'required|numeric|min:1'
        ]);

        $amount = (float) $request->input('shares_amount');
        $sharePrice = (float) \App\Models\Setting::get('sys_share_price', 10000);

        if ($sharePrice <= 0) {
            return redirect()->back()->withErrors(['shares_amount' => 'The share price has not been configured.']);
        }
        if (fmod($amount, $sharePrice) > 0.005) {
            return redirect()->back()->withErrors(['shares_amount' => 'Share purchases must be in multiples of the UGX '.number_format($sharePrice, 2).' share price.'])->withInput();
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($member, $amount) {
                $member = Member::lockForUpdate()->findOrFail($member->id);

                if ((float) $member->available_savings < $amount) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['shares_amount' => 'Insufficient available savings to purchase these shares. Guarantor and loan liens are excluded.']);
                }

                $account = $member->accounts()
                    ->whereHas('product', function ($q) {
                        $q->where('name', 'not like', '%Share Capital%');
                    })->lockForUpdate()->first() ?? $member->accounts()->lockForUpdate()->first();

                if (! $account) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['shares_amount' => 'This member has no account to debit.']);
                }
                if ((float) $account->balance < $amount) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['shares_amount' => 'The account balance cannot cover this purchase.']);
                }

                $account->balance -= $amount;
                $account->save();

                $member->total_savings -= $amount;
                $member->share_capital += $amount;
                $member->save();

                \App\Models\Transaction::create([
                    'reference' => 'SHR-'.\Illuminate\Support\Str::random(6),
                    'account_number' => $account->account_number,
                    'transaction_type' => 'share_transfer',
                    'amount' => $amount,
                    'narration' => 'Purchase of Non-Withdrawable Share Capital',
                    'performed_by_username' => auth()->user() ? auth()->user()->username : 'System',
                ]);

                \App\Services\LedgerService::post('SHR', 'Share capital purchase - '.$member->membership_number, [
                    ['code' => '2000', 'debit' => $amount],
                    ['code' => '3000', 'credit' => $amount],
                ]);

                \App\Services\MemberCapitalService::refresh($member);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect()->back()->with('success', 'Successfully purchased UGX '.number_format($amount).' in shares. This equity is now locked.');
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
            return redirect()->back()->withErrors(['error' => 'Cannot exit SACCO while having active, pending or defaulted loans.']);
        }

        // Verify the member is not still guaranteeing someone else's loan
        $guaranteeLiability = $member->guarantorPledges()
            ->whereIn('status', ['active', 'frozen', 'partially_enforced'])
            ->get()
            ->sum(fn ($p) => max(0, (float) $p->pledged_amount - (float) $p->amount_enforced));
        if ($guaranteeLiability > 0) {
            return redirect()->back()->withErrors(['error' => 'Cannot exit SACCO while guaranteeing loans of UGX '.number_format($guaranteeLiability, 2).'.']);
        }

        $noticeDays = (int) \App\Models\Setting::get('exit_notice_days', 60);

        if (! $member->exit_requested_at) {
            $member->update([
                'exit_requested_at' => now(),
                'notice_period_end' => now()->addDays($noticeDays)->toDateString(),
            ]);

            return redirect()->back()->with('success', 'Exit request recorded. Under the '.$noticeDays.'-day notice period the member remains active until '.$member->notice_period_end->format('d M Y').'.');
        }

        if ($member->notice_period_end && \Carbon\Carbon::parse($member->notice_period_end)->isFuture()) {
            return redirect()->back()->withErrors(['error' => 'The notice period has not elapsed. Exit can only be completed on or after '.$member->notice_period_end->format('d M Y').'.']);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($member) {
            $member = Member::lockForUpdate()->findOrFail($member->id);

            $refund = (float) $member->total_savings + (float) $member->share_capital;
            $account = $member->accounts()->lockForUpdate()->first();

            if ($account && $refund > 0) {
                \App\Models\Transaction::create([
                    'reference' => 'EXT-'.\Illuminate\Support\Str::random(6),
                    'account_number' => $account->account_number,
                    'transaction_type' => 'withdrawal',
                    'amount' => $refund,
                    'narration' => 'Full Member Exit Refund (Savings + Share Capital)',
                    'performed_by_username' => auth()->user() ? auth()->user()->username : 'System',
                ]);

                \App\Services\LedgerService::post('EXT', 'Member exit refund - '.$member->membership_number, [
                    ['code' => '2000', 'debit' => (float) $member->total_savings],
                    ['code' => '3000', 'debit' => (float) $member->share_capital],
                    ['code' => '1000', 'credit' => $refund],
                ]);
            }

            $member->accounts()->update(['balance' => 0, 'is_active' => false]);

            $member->total_savings = 0;
            $member->share_capital = 0;
            $member->status = 'Exited';
            $member->exit_date = now()->toDateString();
            $member->save();

            \App\Models\GuarantorPledge::where('guarantor_member_id', $member->id)
                ->whereIn('status', ['frozen', 'active'])
                ->update(['status' => 'released']);

            \App\Services\MemberCapitalService::refresh($member);
        });

        return redirect()->back()->with('success', 'Member formally exited from the SACCO. Savings and share capital refunded.');
    }
}
