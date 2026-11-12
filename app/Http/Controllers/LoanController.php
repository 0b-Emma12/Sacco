<?php

namespace App\Http\Controllers;

use App\Models\Collateral;
use App\Models\GuarantorPledge;
use App\Models\Loan;
use App\Models\LoanAppraisal;
use App\Models\LoanProduct;
use App\Models\LoanRestructuring;
use App\Models\LoanWriteOff;
use App\Models\Member;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\LedgerService;
use App\Services\MemberCapitalService;
use App\Services\RepaymentScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoanController extends Controller
{
    public function index(Request $request) {
        $query = Loan::with(['member', 'product']);

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('member', function ($m) use ($search) {
                      $m->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('membership_number', 'like', "%{$search}%");
                  });
            });
        }

        $summary = [
            'total' => (clone $query)->count(),
            'portfolio' => (float) (clone $query)
                ->whereIn('status', ['disbursed', 'partially_paid', 'defaulted'])
                ->sum('principal_amount'),
            'pending' => (clone $query)->whereIn('status', ['submitted', 'under_review'])->count(),
            'defaulted' => (clone $query)->where('status', 'defaulted')->count(),
        ];

        $loans = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        return view('loans.index', compact('loans', 'summary'));
    }

    public function create() {
        $members = Member::where('status', 'Active')->get();
        $products = LoanProduct::where('is_active', true)->orderBy('name')->get();
        $applicationFee = (float) Setting::get('loan_application_fee', 5000);

        return view('loans.create', compact('members', 'products', 'applicationFee'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'product_id' => 'required|exists:loan_products,id',
            'principal_amount' => 'required|numeric|min:1',
            'term_months' => 'required|integer|min:1',
            'guarantor_1_id' => 'required|exists:members,id|different:member_id',
            'guarantor_1_amount' => 'required|numeric|min:1',
            'guarantor_1_consent' => 'accepted',
        ], [
            'guarantor_1_consent.accepted' => 'The guarantor must consent to the pledge before the application can be submitted.',
        ]);

        $fee = (float) Setting::get('loan_application_fee', 5000);

        DB::transaction(function () use ($validated, $fee) {
            $product = LoanProduct::lockForUpdate()->findOrFail($validated['product_id']);
            if (! $product->is_active) {
                throw ValidationException::withMessages(['product_id' => 'This loan product is not available.']);
            }

            $borrower = Member::lockForUpdate()->findOrFail($validated['member_id']);
            if ($borrower->status !== 'Active') {
                throw ValidationException::withMessages(['member_id' => 'Only active members may apply for a loan.']);
            }

            $principal = (float) $validated['principal_amount'];
            $term = (int) $validated['term_months'];

            if ($principal < (float) $product->min_amount) {
                throw ValidationException::withMessages(['principal_amount' => 'The minimum amount for '.$product->name.' is UGX '.number_format((float) $product->min_amount)]);
            }
            if ($product->max_amount !== null && $principal > (float) $product->max_amount) {
                throw ValidationException::withMessages(['principal_amount' => 'The maximum amount for '.$product->name.' is UGX '.number_format((float) $product->max_amount)]);
            }
            if ($term > (int) $product->max_tenure_months) {
                throw ValidationException::withMessages(['term_months' => 'The maximum term for '.$product->name.' is '.$product->max_tenure_months.' months.']);
            }

            $available = (float) $borrower->available_savings;
            $maxLoanAmount = $available * (float) $product->multiplier_factor;
            if ($principal > $maxLoanAmount) {
                throw ValidationException::withMessages([
                    'principal_amount' => 'Requested amount exceeds the maximum multiplier ('.$product->multiplier_factor.'x available savings of UGX '.number_format($available, 2).'). Max allowed: UGX '.number_format($maxLoanAmount, 2),
                ]);
            }
            if ($available < $fee) {
                throw ValidationException::withMessages([
                    'member_id' => 'Member has insufficient available savings to pay the UGX '.number_format($fee).' loan application fee.',
                ]);
            }

            $guarantor = Member::lockForUpdate()->findOrFail($validated['guarantor_1_id']);
            if ($guarantor->status !== 'Active') {
                throw ValidationException::withMessages(['guarantor_1_id' => 'The selected guarantor must be an active member.']);
            }

            $pledgeAmount = (float) $validated['guarantor_1_amount'];
            $guarantorCapacity = (float) $guarantor->available_savings;
            if ($pledgeAmount > $guarantorCapacity) {
                throw ValidationException::withMessages([
                    'guarantor_1_amount' => 'The guarantor can only pledge up to UGX '.number_format($guarantorCapacity, 2).' of available savings.',
                ]);
            }
            if ($pledgeAmount < $principal) {
                throw ValidationException::withMessages([
                    'guarantor_1_amount' => 'Social collateral must cover the full principal of UGX '.number_format($principal, 2).'.',
                ]);
            }

            $borrowerAccount = $borrower->accounts()->lockForUpdate()->first();
            if (! $borrowerAccount) {
                throw ValidationException::withMessages(['member_id' => 'Member has no savings account to charge the application fee to.']);
            }
            if ((float) $borrowerAccount->balance < $fee) {
                throw ValidationException::withMessages(['member_id' => 'The member\'s account balance cannot cover the UGX '.number_format($fee).' application fee.']);
            }

            $borrowerAccount->balance -= $fee;
            $borrowerAccount->save();

            $borrower->total_savings -= $fee;
            $borrower->save();

            Transaction::create([
                'reference' => 'FEE-'.strtoupper(Str::random(6)),
                'account_number' => $borrowerAccount->account_number,
                'transaction_type' => 'fee',
                'amount' => $fee,
                'narration' => 'Non-refundable Loan Application Fee',
                'performed_by_username' => auth()->user() ? auth()->user()->username : 'System',
            ]);

            LedgerService::post('FEE', 'Loan application fee - '.$borrower->membership_number, [
                ['code' => '2000', 'debit' => $fee],
                ['code' => '4100', 'credit' => $fee],
            ]);

            $loan = Loan::create([
                'member_id' => $borrower->id,
                'product_id' => $product->id,
                'principal_amount' => $principal,
                'interest_rate' => $product->interest_rate_monthly,
                'interest_method' => $product->interest_method,
                'term_months' => $term,
                'status' => 'under_review',
                'amount_paid' => 0,
                'applied_by' => auth()->user() ? auth()->user()->username : 'System',
            ]);

            GuarantorPledge::create([
                'loan_id' => $loan->id,
                'guarantor_member_id' => $guarantor->id,
                'pledged_amount' => $pledgeAmount,
                'guarantee_type' => 'FULL',
                'status' => 'active',
                'consent_given' => false,
                'consent_sms_sent' => false,
                'consent_confirmed_at' => null,
            ]);

            MemberCapitalService::refresh($borrower);
            MemberCapitalService::refresh($guarantor);
        });

        return redirect()->route('loans.index')->with('success', 'Loan application submitted. UGX '.number_format($fee).' application fee posted.');
    }

    public function show($id) {
        $loan = Loan::with([
            'member', 'product', 'guarantorPledges.guarantor', 'repayments', 'schedules',
            'appraisal', 'collaterals', 'restructurings', 'writeOffs',
        ])->findOrFail($id);
        return view('loans.show', compact('loan'));
    }

    public function edit($id) { return view('loans.edit', compact('id')); }

    public function update(Request $request, $id) {
        $action = $request->input('action');
        $user = $request->user();

        if (! in_array($action, ['approve', 'reject', 'disburse'], true)) {
            return redirect()->route('loans.index')->withErrors(['action' => 'Unknown loan action.']);
        }

        if (in_array($action, ['approve', 'reject'], true) && ! ($user && $user->canApproveLoans())) {
            abort(403, 'Only an administrator or manager may approve or reject loans.');
        }
        if ($action === 'disburse' && ! ($user && $user->canDisburseLoans())) {
            abort(403, 'Only an administrator, manager or finance officer may disburse loans.');
        }

        if ($action === 'reject') {
            $request->validate(['rejection_reason' => 'required|string|max:500']);
        }

        $loan = Loan::findOrFail($id);

        if ($action === 'approve') {
            if (! in_array($loan->status, ['submitted', 'under_review'], true)) {
                return redirect()->back()->withErrors(['action' => 'Only a submitted or under-review loan can be approved. Current status: '.$loan->status.'.']);
            }

            if ($loan->guarantorPledges()->where('consent_given', false)->count() > 0) {
                return redirect()->back()->withErrors(['action' => 'All guarantors must consent before the loan can be approved.']);
            }

            $appraisal = $loan->appraisal;
            if (! $appraisal) {
                return redirect()->back()->withErrors(['action' => 'Record a credit appraisal (L02) before approving this loan.']);
            }
            if (! $appraisal->allowsApproval()) {
                return redirect()->back()->withErrors(['action' => 'The credit appraisal recommends rejection, so this loan cannot be approved.']);
            }

            DB::transaction(function () use ($loan) {
                $loan->update([
                    'status' => 'approved',
                    'approved_by' => auth()->user() ? auth()->user()->username : 'System',
                ]);
                $loan->guarantorPledges()->update(['status' => 'frozen']);
                MemberCapitalService::refresh($loan->member);
            });

            return redirect()->back()->with('success', 'Loan approved and guarantor funds frozen.');
        }

        if ($action === 'reject') {
            if (! in_array($loan->status, ['submitted', 'under_review'], true)) {
                return redirect()->back()->withErrors(['action' => 'Only a submitted or under-review loan can be rejected. Current status: '.$loan->status.'.']);
            }

            DB::transaction(function () use ($loan, $request) {
                $loan->update([
                    'status' => 'rejected',
                    'rejection_reason' => $request->input('rejection_reason'),
                ]);
                $loan->guarantorPledges()->update(['status' => 'released']);
                $loan->collaterals()->where('status', 'REGISTERED')->update([
                    'status' => 'RELEASED',
                    'released_at' => now(),
                    'released_by' => auth()->user() ? auth()->user()->username : 'System',
                ]);
                MemberCapitalService::refresh($loan->member);
            });

            return redirect()->back()->with('success', 'Loan rejected successfully.');
        }

        if ($loan->status !== 'approved') {
            return redirect()->back()->withErrors(['action' => 'Only an approved loan can be disbursed. Current status: '.$loan->status.'.']);
        }

        DB::transaction(function () use ($loan) {
            $loan = Loan::lockForUpdate()->findOrFail($loan->id);
            if ($loan->status !== 'approved') {
                throw ValidationException::withMessages(['action' => 'This loan is no longer awaiting disbursement.']);
            }

            $product = $loan->product;
            $principal = (float) $loan->principal_amount;
            $processingFee = round($principal * ((float) ($product->processing_fee_pct ?? 0) / 100), 2);
            $insuranceFee = round($principal * ((float) ($product->insurance_fee_pct ?? 0) / 100), 2);
            $fees = $processingFee + $insuranceFee;
            $net = round($principal - $fees, 2);

            if ($net <= 0) {
                throw ValidationException::withMessages(['action' => 'Loan fees exceed the approved principal. Review the loan product configuration.']);
            }

            $account = $loan->member->accounts()->lockForUpdate()->first();
            if (! $account) {
                throw ValidationException::withMessages(['action' => 'The member has no account to credit.']);
            }

            $loan->update([
                'status' => 'disbursed',
                'processing_fee' => $processingFee,
                'insurance_fee' => $insuranceFee,
                'disbursed_at' => now(),
                'disbursed_by' => auth()->user() ? auth()->user()->username : 'System',
                'lien_amount_placed' => min((float) $loan->member->total_savings, $principal),
            ]);

            RepaymentScheduleService::generate($loan);

            $loan->collaterals()->where('status', 'REGISTERED')->update(['status' => 'LIENED']);

            $account->balance += $net;
            $account->save();

            $member = $loan->member;
            $member->total_savings += $net;
            $member->save();

            Transaction::create([
                'reference'             => 'DIS-'.strtoupper(Str::random(6)),
                'account_number'        => $account->account_number,
                'transaction_type'      => 'loan_disbursement',
                'amount'                => $net,
                'narration'             => 'Loan Disbursement - Loan #'.$loan->id.' (net of UGX '.number_format($fees, 2).' fees)',
                'performed_by_username' => auth()->user() ? auth()->user()->username : 'System',
            ]);

            $lines = [
                ['code' => '1200', 'debit' => $principal],
                ['code' => '2000', 'credit' => $net],
            ];
            if ($fees > 0) {
                $lines[] = ['code' => '4100', 'credit' => $fees];
            }

            LedgerService::post('DIS', 'Loan disbursement #'.$loan->id, $lines, 'loan', $loan->id);

            MemberCapitalService::refresh($member);
            foreach ($loan->guarantorPledges()->with('guarantor')->get() as $pledge) {
                if ($pledge->guarantor) {
                    MemberCapitalService::refresh($pledge->guarantor);
                }
            }
        });

        return redirect()->back()->with('success', 'Loan disbursed and repayment schedule generated.');
    }

    public function appraise(Request $request, $id)
    {
        $user = $request->user();
        if (! ($user && $user->canAppraiseLoans())) {
            abort(403, 'Only an administrator, manager or loan officer may appraise a loan.');
        }

        $loan = Loan::with(['guarantorPledges', 'collaterals'])->findOrFail($id);

        if (! in_array($loan->status, ['submitted', 'under_review'], true)) {
            return redirect()->back()->withErrors(['appraisal' => 'An appraisal can only be recorded while the application is under review.']);
        }

        $data = $request->validate([
            'monthly_income' => 'required|numeric|min:1',
            'monthly_expenses' => 'required|numeric|min:0',
            'existing_monthly_obligations' => 'nullable|numeric|min:0',
            'risk_rating' => 'required|in:LOW,MEDIUM,HIGH',
            'recommendation' => 'required|in:APPROVE,APPROVE_WITH_CONDITIONS,REJECT',
            'recommended_amount' => 'nullable|numeric|min:1',
            'recommended_tenure_months' => 'nullable|integer|min:1',
            'conditions' => 'nullable|string|max:1000',
            'remarks' => 'nullable|string|max:1000',
        ]);

        if ($data['recommendation'] !== 'REJECT') {
            if (empty($data['recommended_amount'])) {
                throw ValidationException::withMessages(['recommended_amount' => 'State the amount recommended for approval.']);
            }
            if ((float) $data['recommended_amount'] > (float) $loan->principal_amount) {
                throw ValidationException::withMessages(['recommended_amount' => 'The recommended amount cannot exceed the amount requested.']);
            }
        }

        if ($data['recommendation'] === 'APPROVE_WITH_CONDITIONS' && blank($data['conditions'] ?? null)) {
            throw ValidationException::withMessages(['conditions' => 'List the conditions attached to this approval.']);
        }

        $tenure = (int) ($data['recommended_tenure_months'] ?? $loan->term_months);
        if ($tenure > (int) $loan->term_months) {
            throw ValidationException::withMessages(['recommended_tenure_months' => 'The recommended tenure cannot exceed the requested term.']);
        }

        $amount = (float) ($data['recommended_amount'] ?? 0);
        $obligations = (float) ($data['existing_monthly_obligations'] ?? 0);
        $income = (float) $data['monthly_income'];
        $installment = $this->monthlyInstallment($loan, $amount, $tenure);
        $dsr = $income > 0 ? round((($obligations + $installment) / $income) * 100, 2) : 0;

        $collateralValue = (float) $loan->collaterals
            ->filter(fn ($c) => $c->isHeld())
            ->sum(fn ($c) => $c->securityValue());
        $guarantorCoverage = (float) $loan->guarantorPledges
            ->filter(fn ($p) => in_array($p->status, ['active', 'frozen', 'partially_enforced'], true))
            ->sum(fn ($p) => $p->remainingLiability());

        LoanAppraisal::updateOrCreate(['loan_id' => $loan->id], [
            'appraised_by' => $user->username ?? $user->email,
            'monthly_income' => $income,
            'monthly_expenses' => (float) $data['monthly_expenses'],
            'existing_monthly_obligations' => $obligations,
            'debt_service_ratio' => $dsr,
            'collateral_value' => $collateralValue,
            'guarantor_coverage' => $guarantorCoverage,
            'risk_rating' => $data['risk_rating'],
            'recommendation' => $data['recommendation'],
            'recommended_amount' => $data['recommended_amount'] ?? null,
            'recommended_tenure_months' => $tenure,
            'conditions' => $data['conditions'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'appraised_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Credit appraisal recorded. Debt service ratio: '.number_format($dsr, 2).'%.');
    }

    public function recordGuarantorConsent(Request $request, $id, $pledgeId)
    {
        $user = $request->user();
        if (! ($user && ($user->canAppraiseLoans() || $user->canDisburseLoans()))) {
            abort(403, 'Only a loan officer, manager or administrator may record guarantor consent.');
        }

        $loan = Loan::findOrFail($id);
        if (! in_array($loan->status, ['submitted', 'under_review'], true)) {
            return redirect()->back()->withErrors(['consent' => 'Guarantor consent must be recorded before the loan is approved.']);
        }

        $pledge = $loan->guarantorPledges()->with('guarantor')->findOrFail($pledgeId);
        if ($pledge->consent_given) {
            return redirect()->back()->withErrors(['consent' => 'Consent for this guarantor has already been recorded.']);
        }

        $data = $request->validate([
            'consent_method' => 'required|in:SMS,IN_PERSON',
            'guarantor_signature' => 'required|string|max:100',
            'consent_confirmed_at' => 'nullable|date|before_or_equal:now',
        ]);

        $pledge->update([
            'consent_given' => true,
            'consent_sms_sent' => $data['consent_method'] === 'SMS',
            'consent_confirmed_at' => $data['consent_confirmed_at'] ?? now(),
            'guarantor_signature' => $data['guarantor_signature'],
        ]);

        if ($pledge->guarantor) {
            MemberCapitalService::refresh($pledge->guarantor);
        }

        return redirect()->back()->with('success', 'Guarantor consent recorded for '.($pledge->guarantor->first_name ?? 'the guarantor').' '.($pledge->guarantor->last_name ?? '').'.');
    }

    public function storeCollateral(Request $request, $id)
    {
        $user = $request->user();
        if (! ($user && ($user->canAppraiseLoans() || $user->canDisburseLoans()))) {
            abort(403, 'Only a loan officer, finance officer, manager or administrator may register collateral.');
        }

        $loan = Loan::findOrFail($id);
        if (in_array($loan->status, ['paid', 'rejected'], true)) {
            return redirect()->back()->withErrors(['collateral' => 'Collateral cannot be registered against a closed application.']);
        }

        $data = $request->validate([
            'asset_type' => 'required|string|max:50',
            'description' => 'required|string|max:1000',
            'registration_number' => 'required|string|max:100|unique:collaterals,registration_number',
            'estimated_value' => 'required|numeric|min:1',
            'forced_sale_value' => 'nullable|numeric|min:0|lte:estimated_value',
            'valuer_name' => 'nullable|string|max:255',
            'valuation_date' => 'nullable|date|before_or_equal:today',
            'location' => 'nullable|string|max:255',
        ], [
            'registration_number.unique' => 'This asset is already registered as collateral elsewhere. Double-pledging is not allowed.',
        ]);

        Collateral::create([
            'member_id' => $loan->member_id,
            'loan_id' => $loan->id,
            'asset_type' => $data['asset_type'],
            'description' => $data['description'],
            'registration_number' => $data['registration_number'],
            'estimated_value' => $data['estimated_value'],
            'forced_sale_value' => $data['forced_sale_value'] ?? null,
            'valuer_name' => $data['valuer_name'] ?? null,
            'valuation_date' => $data['valuation_date'] ?? null,
            'location' => $data['location'] ?? null,
            'status' => 'REGISTERED',
            'registered_by' => $user->username ?? $user->email,
        ]);

        return redirect()->back()->with('success', 'Collateral registered and linked to this loan.');
    }

    public function releaseCollateral(Request $request, $id, $collateralId)
    {
        $user = $request->user();
        if (! ($user && $user->canApproveLoans())) {
            abort(403, 'Only an administrator or manager may approve the release of collateral.');
        }

        $loan = Loan::findOrFail($id);
        $collateral = $loan->collaterals()->findOrFail($collateralId);

        if (! $collateral->isHeld()) {
            return redirect()->back()->withErrors(['collateral' => 'This collateral has already been released.']);
        }
        if (! in_array($loan->status, ['paid', 'rejected'], true)) {
            return redirect()->back()->withErrors(['collateral' => 'Collateral can only be released once the loan is fully repaid or rejected.']);
        }

        $collateral->update([
            'status' => 'RELEASED',
            'released_at' => now(),
            'released_by' => $user->username ?? $user->email,
        ]);

        return redirect()->back()->with('success', 'Collateral released to the member.');
    }

    public function requestRestructuring(Request $request, $id)
    {
        $user = $request->user();
        if (! ($user && $user->canAppraiseLoans())) {
            abort(403, 'Only a loan officer, manager or administrator may request a restructuring.');
        }

        $loan = Loan::findOrFail($id);
        if (! in_array($loan->status, ['disbursed', 'partially_paid', 'defaulted'], true)) {
            return redirect()->back()->withErrors(['restructuring' => 'Only a running or defaulted loan can be restructured.']);
        }
        if ($loan->restructurings()->where('status', 'PENDING')->exists()) {
            return redirect()->back()->withErrors(['restructuring' => 'A restructuring request is already awaiting a decision.']);
        }

        $data = $request->validate([
            'reason' => 'required|string|max:1000',
            'new_tenure_months' => 'required|integer|min:1|max:120',
            'grace_period_months' => 'nullable|integer|min:0|max:12',
            'notes' => 'nullable|string|max:1000',
        ]);

        LoanRestructuring::create([
            'loan_id' => $loan->id,
            'reason' => $data['reason'],
            'previous_tenure_months' => (int) $loan->term_months,
            'previous_installment' => (float) $loan->monthly_installment,
            'outstanding_at_request' => RepaymentScheduleService::outstanding($loan),
            'new_tenure_months' => (int) $data['new_tenure_months'],
            'grace_period_months' => (int) ($data['grace_period_months'] ?? 0),
            'decision_notes' => $data['notes'] ?? null,
            'status' => 'PENDING',
            'requested_by' => $user->username ?? $user->email,
        ]);

        return redirect()->back()->with('success', 'Restructuring request submitted for management approval.');
    }

    public function decideRestructuring(Request $request, $id, $restructuringId)
    {
        $user = $request->user();
        if (! ($user && $user->canApproveLoans())) {
            abort(403, 'Only an administrator or manager may decide on a restructuring.');
        }

        $loan = Loan::findOrFail($id);
        $restructuring = $loan->restructurings()->findOrFail($restructuringId);

        if (! $restructuring->isPending()) {
            return redirect()->back()->withErrors(['restructuring' => 'This restructuring request has already been decided.']);
        }

        $data = $request->validate([
            'decision' => 'required|in:APPROVE,REJECT',
            'decision_notes' => 'nullable|string|max:1000',
        ]);

        if ($data['decision'] === 'REJECT') {
            if (blank($data['decision_notes'] ?? null)) {
                throw ValidationException::withMessages(['decision_notes' => 'State why the restructuring is rejected.']);
            }

            $restructuring->update([
                'status' => 'REJECTED',
                'approved_by' => $user->username ?? $user->email,
                'approved_at' => now(),
                'decision_notes' => $data['decision_notes'],
            ]);

            return redirect()->back()->with('success', 'Restructuring request rejected.');
        }

        DB::transaction(function () use ($loan, $restructuring, $user, $data) {
            $loan = Loan::lockForUpdate()->findOrFail($loan->id);

            $installment = RepaymentScheduleService::reschedule(
                $loan,
                (int) $restructuring->new_tenure_months,
                (int) $restructuring->grace_period_months
            );

            $restructuring->update([
                'status' => 'APPROVED',
                'new_installment' => $installment,
                'approved_by' => $user->username ?? $user->email,
                'approved_at' => now(),
                'decision_notes' => $data['decision_notes'] ?? null,
            ]);

            MemberCapitalService::refresh($loan->member);
        });

        return redirect()->back()->with('success', 'Loan restructured. A new repayment schedule has been generated.');
    }

    public function requestWriteOff(Request $request, $id)
    {
        $user = $request->user();
        if (! ($user && $user->canAppraiseLoans())) {
            abort(403, 'Only a loan officer, manager or administrator may request a write-off.');
        }

        $loan = Loan::with('schedules')->findOrFail($id);

        if (! in_array($loan->status, ['disbursed', 'partially_paid', 'defaulted'], true)) {
            return redirect()->back()->withErrors(['write_off' => 'Only a running or defaulted loan can be written off.']);
        }

        $maxOverdue = (int) $loan->schedules->max('days_overdue');
        if ($loan->status !== 'defaulted' && $maxOverdue < 90) {
            return redirect()->back()->withErrors(['write_off' => 'A write-off can only be requested after 90 days in arrears.']);
        }
        if ($loan->writeOffs()->where('status', 'PENDING')->exists()) {
            return redirect()->back()->withErrors(['write_off' => 'A write-off request is already awaiting a board decision.']);
        }

        $data = $request->validate([
            'reason' => 'required|string|max:1000',
            'recovery_efforts' => 'required|array|min:1',
            'recovery_efforts.*' => 'in:'.implode(',', array_keys(LoanWriteOff::RECOVERY_EFFORTS)),
            'amount_recoverable' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $outstanding = RepaymentScheduleService::principalOutstanding($loan);
        $recoverable = (float) ($data['amount_recoverable'] ?? 0);
        if ($recoverable > $outstanding) {
            throw ValidationException::withMessages(['amount_recoverable' => 'Recoverable amount cannot exceed the outstanding principal of UGX '.number_format($outstanding, 2).'.']);
        }

        LoanWriteOff::create([
            'loan_id' => $loan->id,
            'reason' => $data['reason'],
            'recovery_efforts' => array_values($data['recovery_efforts']),
            'amount_recoverable' => $recoverable,
            'write_off_amount' => round(max(0, $outstanding - $recoverable), 2),
            'status' => 'PENDING',
            'requested_by' => $user->username ?? $user->email,
            'decision_notes' => $data['notes'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Write-off request submitted for board approval.');
    }

    public function decideWriteOff(Request $request, $id, $writeOffId)
    {
        $user = $request->user();
        if (! ($user && $user->canApproveLoans())) {
            abort(403, 'Only an administrator or manager may approve a write-off.');
        }

        $loan = Loan::with('guarantorPledges.guarantor')->findOrFail($id);
        $writeOff = $loan->writeOffs()->findOrFail($writeOffId);

        if (! $writeOff->isPending()) {
            return redirect()->back()->withErrors(['write_off' => 'This write-off request has already been decided.']);
        }

        $data = $request->validate([
            'decision' => 'required|in:APPROVE,REJECT',
            'decision_notes' => 'nullable|string|max:1000',
        ]);

        if ($data['decision'] === 'REJECT') {
            if (blank($data['decision_notes'] ?? null)) {
                throw ValidationException::withMessages(['decision_notes' => 'State why the write-off is rejected.']);
            }

            $writeOff->update([
                'status' => 'REJECTED',
                'approved_by' => $user->username ?? $user->email,
                'approved_at' => now(),
                'decision_notes' => $data['decision_notes'],
            ]);

            return redirect()->back()->with('success', 'Write-off request rejected.');
        }

        DB::transaction(function () use ($loan, $writeOff, $user, $data) {
            $loan = Loan::lockForUpdate()->findOrFail($loan->id);

            $outstanding = RepaymentScheduleService::principalOutstanding($loan);
            $recoverable = min((float) $writeOff->amount_recoverable, $outstanding);
            $amount = round(max(0, $outstanding - $recoverable), 2);

            if ($amount > 0) {
                LedgerService::post('WOF', 'Loan write-off #'.$loan->id, [
                    ['code' => '5300', 'debit' => $amount],
                    ['code' => '1200', 'credit' => $amount],
                ], 'loan', $loan->id);
            }

            $loan->update([
                'status' => 'written_off',
                'outstanding_principal' => 0,
                'outstanding_balance' => 0,
            ]);

            $loan->guarantorPledges()->update(['status' => 'released']);
            $loan->collaterals()->whereIn('status', ['REGISTERED', 'LIENED'])->update([
                'status' => 'RELEASED',
                'released_at' => now(),
                'released_by' => $user->username ?? $user->email,
            ]);

            $writeOff->update([
                'status' => 'APPROVED',
                'write_off_amount' => $amount,
                'approved_by' => $user->username ?? $user->email,
                'approved_at' => now(),
                'decision_notes' => $data['decision_notes'] ?? null,
            ]);

            MemberCapitalService::refresh($loan->member);
            foreach ($loan->guarantorPledges as $pledge) {
                if ($pledge->guarantor) {
                    MemberCapitalService::refresh($pledge->guarantor);
                }
            }
        });

        return redirect()->back()->with('success', 'Loan written off. The loss has been posted to the ledger.');
    }

    private function monthlyInstallment(Loan $loan, float $principal, int $months): float
    {
        if ($principal <= 0 || $months <= 0) {
            return 0.0;
        }

        $rate = (float) $loan->interest_rate / 100;

        if (strtoupper((string) $loan->interest_method) === 'FLAT') {
            return round(($principal + ($principal * $rate * $months)) / $months, 2);
        }
        if ($rate <= 0) {
            return round($principal / $months, 2);
        }

        $growth = pow(1 + $rate, $months);

        return round($principal * $rate * $growth / ($growth - 1), 2);
    }

    public function destroy($id) {
        return redirect()->route('loans.index')->with('success', 'Deleted successfully');
    }
}
