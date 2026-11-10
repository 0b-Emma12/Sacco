<?php
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/login', function () { return view('auth.login'); })->name('login')->middleware('guest');
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
Route::get('/register', function () { return 'Register page coming soon!'; })->name('register');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [\App\Http\Controllers\DashboardController::class, 'exportCsv'])->name('dashboard.export');
    
    // Main Resources
    Route::post('members/{member}/buy-shares', [\App\Http\Controllers\MemberController::class, 'buyShares'])->name('members.buyShares');
    Route::post('members/{member}/exit', [\App\Http\Controllers\MemberController::class, 'exitSacco'])->name('members.exitSacco');
    Route::resource('members', \App\Http\Controllers\MemberController::class);
    Route::resource('accounts', \App\Http\Controllers\AccountController::class);
    Route::get('transactions/{transaction}/receipt', [\App\Http\Controllers\TransactionController::class, 'receipt'])->name('transactions.receipt');
    Route::resource('transactions', \App\Http\Controllers\TransactionController::class);
    Route::get('/loans/defaulted', function () { return view('loans.defaulted', ['title' => 'Defaulted Instalments']); })->name('loans.defaulted');
    Route::post('loans/{loan}/repayments', [\App\Http\Controllers\LoanRepaymentController::class, 'store'])->name('loans.repayments.store');
    Route::resource('loans', \App\Http\Controllers\LoanController::class);
    
    // Management Resources
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class);
    Route::resource('accounting', \App\Http\Controllers\AccountingController::class);
    Route::resource('reports', \App\Http\Controllers\ReportController::class);
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::resource('sms', \App\Http\Controllers\SmsController::class);
    Route::resource('emails', \App\Http\Controllers\EmailController::class);
    
    // Company Resources
    Route::resource('documents', \App\Http\Controllers\DocumentController::class);
    Route::resource('settings', \App\Http\Controllers\SettingController::class);
    Route::resource('help', \App\Http\Controllers\HelpController::class);
});
