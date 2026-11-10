# 🏦 Open SACCO Management System (Laravel Edition)
*A comprehensive, monolithic Laravel application for managing Savings and Credit Cooperative Organizations (SACCOs), compliant with Uganda’s Tier 4 Microfinance Institutions and Money Lenders Act (UMRA).*

## 1. Executive Summary
Open SACCO converts collective member liquidity into accessible micro-credit. Unlike commercial banks, it relies on **social collateral**, **share capital**, and **member savings**. This Laravel-based system provides a robust Management Information System (MIS) that handles the entire member lifecycle, double-entry accounting, loan underwriting with guarantor matrices, and automated default recovery protocols, while supporting offline-first operations for rural/semi-urban branches.

---

## 2. Expanded Core Features
*In addition to the base features, the following SACCO-specific modules are included:*

- **Share Capital & Dividend Engine:** Track non-withdrawable equity shares. Automated annual dividend calculation based on net surplus and proportional shareholding.
- **Guarantor Matrix Management:** Track 2–4 guarantors per loan, freeze their pledged savings, and automate liquidation/deduction during defaults.
- **Incubation Period Tracker:** Enforce mandatory 3–6 month savings accumulation before unlocking credit eligibility (e.g., max loan = 3x total savings).
- **Mobile Money Integration:** Native hooks for MTN MoMo and Airtel Money APIs for disbursements and repayment collections.
- **UMRA Compliance Reporting:** Pre-built reports for Tier 4 regulatory submissions (Liquidity ratios, NPL ratios, Member registers).
- **Audit Trail:** Immutable logging of all financial actions, approvals, and ledger modifications.

---

## 3. Tech Stack & Architecture
- **Backend:** Laravel 8.x+ (PHP 8.0+)
- **Frontend:** Laravel Blade Templates + Alpine.js (for lightweight interactivity) + Tailwind CSS
- **Database:** SQLite (Local branch development/offline) → MySQL/PostgreSQL (Central Cloud Production)
- **Offline-First Strategy:** Branches run a local Laravel + SQLite instance. A custom `SyncController` pushes/pulls encrypted JSON payloads to the central cloud server when internet connectivity is restored.
- **Authentication:** Laravel Breeze/Jetstream with Role-Based Access Control (RBAC) via Spatie Permission.

---

## 4. Critical Database Schema Design (Eloquent Models)
To support the Ugandan SACCO model, your database must include these specific tables:

```php
// 1. Members Table
Schema::create('members', function (Blueprint $table) {
    $table->id();
    $table->string('national_id')->unique(); // KYC
    $table->string('full_name');
    $table->date('registration_date');
    $table->enum('status', ['prospective', 'incubating', 'active', 'suspended', 'exited']);
    $table->decimal('total_savings', 15, 2)->default(0);
    $table->decimal('share_capital', 15, 2)->default(0);
    $table->decimal('credit_limit', 15, 2)->default(0); // Auto-calculated: 3x savings
    $table->timestamps();
});

// 2. Guarantor Pledges Table (The core of social collateral)
Schema::create('guarantor_pledges', function (Blueprint $table) {
    $table->id();
    $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
    $table->foreignId('guarantor_member_id')->constrained('members');
    $table->decimal('pledged_amount', 15, 2);
    $table->enum('status', ['active', 'frozen', 'liquidated', 'released'])->default('active');
    $table->timestamps();
});

// 3. Loans Table
Schema::create('loans', function (Blueprint $table) {
    $table->id();
    $table->foreignId('member_id')->constrained();
    $table->decimal('principal_amount', 15, 2);
    $table->decimal('interest_rate', 5, 2); // e.g., 5% per month
    $table->integer('term_months');
    $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'disbursed', 'defaulted', 'closed']);
    $table->integer('default_stage')->default(0); // 0: None, 1: Alert, 2: Self-Liquidation, 3: Guarantor Deduction, 4: External
    $table->timestamps();
});

// 4. Double-Entry Journal Entries
Schema::create('journal_entries', function (Blueprint $table) {
    $table->id();
    $table->string('reference_type'); // e.g., 'loan_disbursement', 'savings_deposit'
    $table->unsignedBigInteger('reference_id');
    $table->foreignId('account_id')->constrained('chart_of_accounts'); // e.g., Asset: Cash, Liability: Member Savings
    $table->enum('type', ['debit', 'credit']);
    $table->decimal('amount', 15, 2);
    $table->text('description')->nullable();
    $table->timestamps();
});
```

---

## 5. End-to-End Operational Workflows (Mapped to Laravel)

### Phase 1: Onboarding & Incubation
1. **Action:** Admin creates a `Member` record, uploads National ID (KYC), and records Entry Fee + Share Capital purchase.
2. **System Logic:** Member status is set to `incubating`. The system blocks loan applications until `registration_date` is ≥ 3 months and `total_savings` > minimum threshold.
3. **Accounting:** 
   - Debit: Cash/Bank 
   - Credit: Share Capital Equity / Member Savings Liability

### Phase 2: Loan Application & Guarantor Matrix
1. **Action:** Active member applies for a loan. System validates `requested_amount <= (total_savings * 3)`.
2. **Guarantor Selection:** Applicant selects 2–4 existing members. 
3. **System Logic:** 
   - Check if guarantors have sufficient *unfrozen* savings.
   - Create `guarantor_pledges` records.
   - **Freeze** the pledged amount in the guarantors' accounts (prevents withdrawal).
4. **Committee Review:** Loan Officer/Manager changes status to `approved` via a Blade UI with digital signature confirmation.

### Phase 3: Disbursement & Repayment
1. **Disbursement:** On approval, funds are sent via Mobile Money API or Bank Transfer.
2. **Accounting:** 
   - Debit: Loans Receivable (Asset)
   - Credit: Cash/Bank (Asset)
3. **Repayment:** Member pays via Mobile Money. Webhook updates the loan schedule. System automatically unfreezes guarantor pledges proportionally as the principal is paid down.

### Phase 4: Automated Default Recovery Protocol
A scheduled Laravel Command (`php artisan sacco:check-defaults`) runs daily:
- **Stage 1 (Days 1-30 overdue):** System sends SMS to defaulter and all guarantors. Status: `defaulted`, stage: `1`.
- **Stage 2 (Days 31-60):** System automatically creates a journal entry to liquidate the *defaulter’s own savings*. Status stage: `2`.
- **Stage 3 (Days 61-90):** System iterates through `guarantor_pledges` for this loan, deducting the remaining balance from guarantors' savings, updating their pledge status to `liquidated`. Status stage: `3`.
- **Stage 4 (90+ days):** Flagged for external debt collection. Status stage: `4`.

---

## 6. Offline-First & Sync Strategy (Crucial for Rural Uganda)
Since Laravel is server-side, true offline-first requires a specific architecture:
1. **Local Branch Setup:** Each branch runs a local Laravel instance with a `database.sqlite` file on a local server/laptop.
2. **Local Operations:** All deposits, withdrawals, and loan approvals work flawlessly without internet.
3. **Sync Mechanism:** 
   - Create a `sync_logs` table to track unsynced transactions.
   - When internet is detected, a background job (`php artisan sacco:sync-to-cloud`) packages new records into encrypted JSON.
   - Sends payload to the Central Cloud Laravel API via Laravel Sanctum authentication.
   - Central server validates, resolves conflicts (using timestamp-based last-write-wins or manual review queues), and sends back updated central ledger states.

---

## 7. Expanded Project Structure
```text
open-sacco-laravel/
├── app/
│   ├── Console/Commands/      # CheckDefaults, CalculateDividends, SyncToCloud
│   ├── Http/Controllers/
│   │   ├── LoanController.php # Handles guarantor matrix & approval workflows
│   │   ├── SyncController.php # Handles offline-to-online data sync
│   │   └── AccountingController.php # Double-entry ledger generation
│   ├── Models/
│   │   ├── Member.php
│   │   ├── Loan.php
│   │   ├── GuarantorPledge.php
│   │   └── JournalEntry.php
│   └── Services/
│       ├── MobileMoneyService.php # MTN/Airtel API wrappers
│       └── DividendCalculator.php
├── database/
│   ├── migrations/          # Includes guarantor_pledges, journal_entries, etc.
│   ├── seeders/             # ChartOfAccountsSeeder, UMRAReportTemplatesSeeder
│   └── database.sqlite      # Local branch database
├── resources/
│   └── views/
│       ├── loans/
│       │   ├── create.blade.php     # Includes guarantor selection UI
│       │   └── guarantors.blade.php # Visualizes frozen vs available savings
│       └── reports/
│           └── umra-compliance.blade.php
└── routes/
    ├── web.php              # Standard UI routes
    └── api.php              # Sanctum-protected routes for mobile money webhooks & sync
```

---

## 8. Getting Started (Enhanced)

1. **Clone & Install:**
   ```bash
   git clone https://github.com/your-org/open-sacco-laravel.git
   cd open-sacco-laravel
   composer install
   npm install && npm run build # For Tailwind CSS
   ```
2. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Ensure `DB_CONNECTION=sqlite` and `touch database/database.sqlite` for local branch dev.*
3. **Migrate & Seed:**
   ```bash
   php artisan migrate --seed
   ```
   *(The seeder should populate a standard SACCO Chart of Accounts: Assets, Liabilities, Equity, Income, Expenses).*
4. **Run Local Server:**
   ```bash
   php artisan serve
   ```

---

## 9. UMRA Compliance & Reporting Module
Your system must generate the following reports natively via Blade/PDF export:
1. **Member Register:** Active, suspended, and exited members with KYC status.
2. **Portfolio at Risk (PAR) Report:** Loans overdue by 30, 60, and 90+ days (critical for Tier 4).
3. **Liquidity Ratio Report:** (Total Liquid Assets / Total Short-term Liabilities) to ensure the SACCO can meet withdrawal demands.
4. **Guarantor Exposure Report:** Shows how much of each member's savings is currently frozen as collateral for others.

---

## 10. Next Steps for Development
1. **Build the Chart of Accounts Seeder:** Define the core double-entry accounts (e.g., `1000: Cash`, `1200: Loans Receivable`, `2000: Member Savings`, `3000: Share Capital`).
2. **Implement the Guarantor Freeze Logic:** Ensure that when a `GuarantorPledge` is created, the `Member` model's `available_savings` is reduced, even though `total_savings` remains the same.
3. **Integrate a Mobile Money Sandbox:** Use the MTN MoMo Open API sandbox to test disbursement and collection webhooks.
4. **Develop the Sync API:** Create the `api/sync` endpoint to handle batched offline transactions from branch offices.
