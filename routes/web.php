<?php
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/login', function () { return view('auth.login'); })->name('login')->middleware('guest');
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
Route::get('/register', function () { return 'Register page coming soon!'; })->name('register');

// Public authenticity check for printed documents (QR target).
Route::get('verify/{document}/{token}', [\App\Http\Controllers\PrintDocumentController::class, 'verify'])->name('print.verify');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [\App\Http\Controllers\DashboardController::class, 'exportCsv'])->name('dashboard.export');
    
    // Main Resources
    Route::get('members/export', [\App\Http\Controllers\MemberController::class, 'export'])->name('members.export');
    Route::post('members/{member}/buy-shares', [\App\Http\Controllers\MemberController::class, 'buyShares'])->name('members.buyShares');
    Route::post('members/{member}/kyc', [\App\Http\Controllers\MemberController::class, 'verifyKyc'])->name('members.kyc');
    Route::post('members/{member}/exit', [\App\Http\Controllers\MemberController::class, 'exitSacco'])->name('members.exitSacco');
    Route::get('members/{member}/death-claim', [\App\Http\Controllers\DeathClaimController::class, 'create'])->name('member-services.death-claims.create');
    Route::post('members/{member}/death-claim', [\App\Http\Controllers\DeathClaimController::class, 'store'])->name('member-services.death-claims.store');
    Route::resource('members', \App\Http\Controllers\MemberController::class);
    Route::resource('accounts', \App\Http\Controllers\AccountController::class);
    Route::get('transactions/{transaction}/receipt', [\App\Http\Controllers\TransactionController::class, 'receipt'])->name('transactions.receipt');
    Route::resource('transactions', \App\Http\Controllers\TransactionController::class);
    Route::get('/loans/defaulted', function () { return view('loans.defaulted', ['title' => 'Defaulted Instalments']); })->name('loans.defaulted');
    Route::post('loans/{loan}/repayments', [\App\Http\Controllers\LoanRepaymentController::class, 'store'])->name('loans.repayments.store');
    Route::post('loans/{loan}/appraisal', [\App\Http\Controllers\LoanController::class, 'appraise'])->name('loans.appraise');
    Route::post('loans/{loan}/guarantors/{pledge}/consent', [\App\Http\Controllers\LoanController::class, 'recordGuarantorConsent'])->name('loans.guarantors.consent');
    Route::post('loans/{loan}/collateral', [\App\Http\Controllers\LoanController::class, 'storeCollateral'])->name('loans.collateral.store');
    Route::post('loans/{loan}/collateral/{collateral}/release', [\App\Http\Controllers\LoanController::class, 'releaseCollateral'])->name('loans.collateral.release');
    Route::post('loans/{loan}/restructuring', [\App\Http\Controllers\LoanController::class, 'requestRestructuring'])->name('loans.restructuring.store');
    Route::post('loans/{loan}/restructuring/{restructuring}/decision', [\App\Http\Controllers\LoanController::class, 'decideRestructuring'])->name('loans.restructuring.decide');
    Route::post('loans/{loan}/write-off', [\App\Http\Controllers\LoanController::class, 'requestWriteOff'])->name('loans.writeoff.store');
    Route::post('loans/{loan}/write-off/{writeOff}/decision', [\App\Http\Controllers\LoanController::class, 'decideWriteOff'])->name('loans.writeoff.decide');
    Route::resource('loans', \App\Http\Controllers\LoanController::class);
    
    // Management Resources
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class);
    Route::get('reports/export', [\App\Http\Controllers\ReportController::class, 'export'])->name('reports.export');
    Route::get('reports/umra', [\App\Http\Controllers\ReportController::class, 'umra'])->name('reports.umra');
    Route::get('reports/umra/export', [\App\Http\Controllers\ReportController::class, 'umraExport'])->name('reports.umra.export');
    Route::resource('accounting', \App\Http\Controllers\AccountingController::class);
    Route::resource('reports', \App\Http\Controllers\ReportController::class);

    // Governance (G01 minutes, G03 AGM, G04 policies)
    Route::get('governance', [\App\Http\Controllers\GovernanceController::class, 'index'])->name('governance.index');
    Route::get('governance/meetings/create/{type}', [\App\Http\Controllers\GovernanceController::class, 'createMeeting'])->name('governance.meetings.create');
    Route::post('governance/meetings', [\App\Http\Controllers\GovernanceController::class, 'storeMeeting'])->name('governance.meetings.store');
    Route::get('governance/meetings/{meeting}', [\App\Http\Controllers\GovernanceController::class, 'showMeeting'])->name('governance.meetings.show');
    Route::post('governance/meetings/{meeting}/approve', [\App\Http\Controllers\GovernanceController::class, 'approveMeeting'])->name('governance.meetings.approve');
    Route::get('governance/policies/create', [\App\Http\Controllers\GovernanceController::class, 'createPolicy'])->name('governance.policies.create');
    Route::post('governance/policies', [\App\Http\Controllers\GovernanceController::class, 'storePolicy'])->name('governance.policies.store');
    Route::get('governance/policies/{policy}', [\App\Http\Controllers\GovernanceController::class, 'showPolicy'])->name('governance.policies.show');
    Route::post('governance/policies/{policy}/approve', [\App\Http\Controllers\GovernanceController::class, 'approvePolicy'])->name('governance.policies.approve');

    // Treasury (F04 bank reconciliation, F05 petty cash, F06 dividends)
    Route::get('treasury/bank-reconciliations', [\App\Http\Controllers\BankReconciliationController::class, 'index'])->name('treasury.bank-reconciliations.index');
    Route::get('treasury/bank-reconciliations/create', [\App\Http\Controllers\BankReconciliationController::class, 'create'])->name('treasury.bank-reconciliations.create');
    Route::post('treasury/bank-reconciliations', [\App\Http\Controllers\BankReconciliationController::class, 'store'])->name('treasury.bank-reconciliations.store');
    Route::get('treasury/bank-reconciliations/{bankReconciliation}', [\App\Http\Controllers\BankReconciliationController::class, 'show'])->name('treasury.bank-reconciliations.show');
    Route::post('treasury/bank-reconciliations/{bankReconciliation}/review', [\App\Http\Controllers\BankReconciliationController::class, 'review'])->name('treasury.bank-reconciliations.review');

    Route::get('treasury/petty-cash', [\App\Http\Controllers\PettyCashController::class, 'index'])->name('treasury.petty-cash.index');
    Route::get('treasury/petty-cash/create', [\App\Http\Controllers\PettyCashController::class, 'create'])->name('treasury.petty-cash.create');
    Route::post('treasury/petty-cash', [\App\Http\Controllers\PettyCashController::class, 'store'])->name('treasury.petty-cash.store');
    Route::post('treasury/petty-cash/replenish', [\App\Http\Controllers\PettyCashController::class, 'replenish'])->name('treasury.petty-cash.replenish');
    Route::post('treasury/petty-cash/{voucher}/approve', [\App\Http\Controllers\PettyCashController::class, 'approve'])->name('treasury.petty-cash.approve');
    Route::post('treasury/petty-cash/{voucher}/reject', [\App\Http\Controllers\PettyCashController::class, 'reject'])->name('treasury.petty-cash.reject');

    Route::get('treasury/dividends', [\App\Http\Controllers\DividendController::class, 'index'])->name('treasury.dividends.index');
    Route::post('treasury/dividends', [\App\Http\Controllers\DividendController::class, 'store'])->name('treasury.dividends.store');
    Route::get('treasury/dividends/{dividend}', [\App\Http\Controllers\DividendController::class, 'show'])->name('treasury.dividends.show');
    Route::post('treasury/dividends/{dividend}/recompute', [\App\Http\Controllers\DividendController::class, 'recompute'])->name('treasury.dividends.recompute');
    Route::post('treasury/dividends/{dividend}/approve', [\App\Http\Controllers\DividendController::class, 'approve'])->name('treasury.dividends.approve');
    Route::post('treasury/dividends/{dividend}/post', [\App\Http\Controllers\DividendController::class, 'post'])->name('treasury.dividends.post');

    // Member services (ST01 death claim, ST02 share transfer)
    Route::get('member-services/death-claims', [\App\Http\Controllers\DeathClaimController::class, 'index'])->name('member-services.death-claims.index');
    Route::get('member-services/death-claims/{deathClaim}', [\App\Http\Controllers\DeathClaimController::class, 'show'])->name('member-services.death-claims.show');
    Route::post('member-services/death-claims/{deathClaim}/approve', [\App\Http\Controllers\DeathClaimController::class, 'approve'])->name('member-services.death-claims.approve');
    Route::post('member-services/death-claims/{deathClaim}/settle', [\App\Http\Controllers\DeathClaimController::class, 'settle'])->name('member-services.death-claims.settle');

    Route::get('member-services/share-transfers', [\App\Http\Controllers\ShareTransferController::class, 'index'])->name('member-services.share-transfers.index');
    Route::get('member-services/share-transfers/create', [\App\Http\Controllers\ShareTransferController::class, 'create'])->name('member-services.share-transfers.create');
    Route::post('member-services/share-transfers', [\App\Http\Controllers\ShareTransferController::class, 'store'])->name('member-services.share-transfers.store');
    Route::get('member-services/share-transfers/{shareTransfer}', [\App\Http\Controllers\ShareTransferController::class, 'show'])->name('member-services.share-transfers.show');
    Route::post('member-services/share-transfers/{shareTransfer}/approve', [\App\Http\Controllers\ShareTransferController::class, 'approve'])->name('member-services.share-transfers.approve');
    Route::post('member-services/share-transfers/{shareTransfer}/reject', [\App\Http\Controllers\ShareTransferController::class, 'reject'])->name('member-services.share-transfers.reject');

    // Standing orders (S05 mandate)
    Route::get('member-services/standing-orders', [\App\Http\Controllers\StandingOrderController::class, 'index'])->name('member-services.standing-orders.index');
    Route::get('member-services/standing-orders/create', [\App\Http\Controllers\StandingOrderController::class, 'create'])->name('member-services.standing-orders.create');
    Route::post('member-services/standing-orders', [\App\Http\Controllers\StandingOrderController::class, 'store'])->name('member-services.standing-orders.store');
    Route::get('member-services/standing-orders/{standingOrder}', [\App\Http\Controllers\StandingOrderController::class, 'show'])->name('member-services.standing-orders.show');
    Route::post('member-services/standing-orders/{standingOrder}/approve', [\App\Http\Controllers\StandingOrderController::class, 'approve'])->name('member-services.standing-orders.approve');
    Route::post('member-services/standing-orders/{standingOrder}/suspend', [\App\Http\Controllers\StandingOrderController::class, 'suspend'])->name('member-services.standing-orders.suspend');
    Route::post('member-services/standing-orders/{standingOrder}/resume', [\App\Http\Controllers\StandingOrderController::class, 'resume'])->name('member-services.standing-orders.resume');
    Route::post('member-services/standing-orders/{standingOrder}/cancel', [\App\Http\Controllers\StandingOrderController::class, 'cancel'])->name('member-services.standing-orders.cancel');
    Route::post('member-services/standing-orders/{standingOrder}/post-due', [\App\Http\Controllers\StandingOrderController::class, 'postDue'])->name('member-services.standing-orders.post-due');

    // Printable documents (Category 1 member-facing documents)
    Route::get('prints/members/{member}/welcome-pack', [\App\Http\Controllers\PrintDocumentController::class, 'welcome'])->name('prints.members.welcome');
    Route::get('prints/members/{member}/share-certificate', [\App\Http\Controllers\PrintDocumentController::class, 'shareCertificate'])->name('prints.members.share-certificate');
    Route::get('prints/members/{member}/statement', [\App\Http\Controllers\PrintDocumentController::class, 'statement'])->name('prints.members.statement');
    Route::get('prints/members/{member}/dividends/{dividend}', [\App\Http\Controllers\PrintDocumentController::class, 'dividendStatement'])->name('prints.members.dividend');
    Route::get('prints/members/{member}/dividends/{dividend}/wht-certificate', [\App\Http\Controllers\PrintDocumentController::class, 'whtCertificate'])->name('prints.members.wht');
    Route::get('prints/loans/{loan}/disbursement-voucher', [\App\Http\Controllers\PrintDocumentController::class, 'disbursementVoucher'])->name('prints.loans.disbursement');
    Route::get('prints/loans/{loan}/repayments/{repayment}', [\App\Http\Controllers\PrintDocumentController::class, 'repaymentReceipt'])->name('prints.loans.repayment');
    Route::get('prints/loans/{loan}/clearance', [\App\Http\Controllers\PrintDocumentController::class, 'clearance'])->name('prints.loans.clearance');
    Route::get('prints/loans/{loan}/guarantor-release', [\App\Http\Controllers\PrintDocumentController::class, 'guarantorRelease'])->name('prints.loans.guarantor-release');
    Route::get('prints/statements', [\App\Http\Controllers\PrintDocumentController::class, 'statements'])->name('prints.statements');
    Route::get('prints/transactions', [\App\Http\Controllers\PrintDocumentController::class, 'transactionLedger'])->name('prints.transactions');
    Route::get('prints/dividend-statements/{distribution}', [\App\Http\Controllers\PrintDocumentController::class, 'dividendStatements'])->name('prints.dividends.bulk');
    Route::get('prints/expenses/{expense}', [\App\Http\Controllers\PrintDocumentController::class, 'expenseVoucher'])->name('prints.expenses.voucher');
    Route::get('prints/death-claims/{claim}', [\App\Http\Controllers\PrintDocumentController::class, 'deathSettlement'])->name('prints.death-claims.settlement');
    Route::get('prints/share-transfers/{shareTransfer}', [\App\Http\Controllers\PrintDocumentController::class, 'shareTransferCertificate'])->name('prints.share-transfers.certificate');
    Route::get('prints/standing-orders/{standingOrder}', [\App\Http\Controllers\PrintDocumentController::class, 'standingOrderMandate'])->name('prints.standing-orders.mandate');

    Route::get('settings/logo', [\App\Http\Controllers\SettingController::class, 'logo'])->name('settings.logo');

    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::resource('sms', \App\Http\Controllers\SmsController::class);
    Route::resource('emails', \App\Http\Controllers\EmailController::class);
    
    // Company Resources
    Route::get('documents/{document}/download', [\App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');
    Route::resource('documents', \App\Http\Controllers\DocumentController::class);
    Route::resource('settings', \App\Http\Controllers\SettingController::class);
    Route::resource('help', \App\Http\Controllers\HelpController::class);
});
