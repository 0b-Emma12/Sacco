<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateLoanRestructuringAndWriteoffTables extends Migration
{
    public $withinTransaction = false;

    private const STATUSES = [
        'draft', 'submitted', 'under_review', 'approved', 'rejected',
        'disbursed', 'partially_paid', 'paid', 'defaulted', 'closed', 'written_off',
    ];

    public function up()
    {
        if (! Schema::hasTable('loan_restructurings')) {
            Schema::create('loan_restructurings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('loan_id');
                $table->text('reason');
                $table->unsignedInteger('previous_tenure_months');
                $table->decimal('previous_installment', 15, 2)->default(0);
                $table->decimal('outstanding_at_request', 15, 2)->default(0);
                $table->unsignedInteger('new_tenure_months');
                $table->unsignedInteger('grace_period_months')->default(0);
                $table->decimal('new_installment', 15, 2)->nullable();
                $table->string('status', 20)->default('PENDING');
                $table->string('requested_by');
                $table->string('approved_by')->nullable();
                $table->dateTime('approved_at')->nullable();
                $table->text('decision_notes')->nullable();
                $table->timestamps();

                $table->foreign('loan_id')->references('id')->on('loans')->cascadeOnDelete();
                $table->index(['loan_id', 'status']);
            });
        }

        if (! Schema::hasTable('loan_write_offs')) {
            Schema::create('loan_write_offs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('loan_id');
                $table->text('reason');
                $table->text('recovery_efforts')->nullable();
                $table->decimal('amount_recoverable', 15, 2)->default(0);
                $table->decimal('write_off_amount', 15, 2)->nullable();
                $table->string('status', 20)->default('PENDING');
                $table->string('requested_by');
                $table->string('approved_by')->nullable();
                $table->dateTime('approved_at')->nullable();
                $table->text('decision_notes')->nullable();
                $table->timestamps();

                $table->foreign('loan_id')->references('id')->on('loans')->cascadeOnDelete();
                $table->index(['loan_id', 'status']);
            });
        }

        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::transaction(fn () => $this->rebuildLoans());
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::statement("ALTER TABLE loans MODIFY status VARCHAR(255) NOT NULL DEFAULT 'draft'");
        }

        DB::table('chart_of_accounts')->insertOrIgnore([
            'code' => '5300',
            'name' => 'Loan Losses Written Off',
            'type' => 'Expense',
            'description' => 'Principal written off after board approval of an unrecoverable loan.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('loan_write_offs');
        Schema::dropIfExists('loan_restructurings');

        $usesAccount = DB::table('journal_entry_lines')->where('account_code', '5300')->exists();
        if (! $usesAccount) {
            DB::table('chart_of_accounts')->where('code', '5300')->delete();
        }

        $legacy = ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'disbursed', 'partially_paid', 'paid', 'defaulted', 'closed'];

        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::table('loans')->where('status', 'written_off')->update(['status' => 'defaulted']);
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::transaction(fn () => $this->rebuildLoans($legacy));
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            DB::table('loans')->where('status', 'written_off')->update(['status' => 'defaulted']);
            DB::statement("ALTER TABLE loans MODIFY status ENUM('draft','submitted','under_review','approved','rejected','disbursed','partially_paid','paid','defaulted','closed') NOT NULL DEFAULT 'draft'");
        }
    }

    private function rebuildLoans(?array $statuses = null): void
    {
        $allowed = collect($statuses ?? self::STATUSES)->map(fn ($status) => "'".$status."'")->implode(', ');

        DB::statement("CREATE TABLE loans_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            member_id VARCHAR(255) NOT NULL,
            principal_amount NUMERIC(15, 2) NOT NULL,
            interest_rate NUMERIC(5, 2) NOT NULL,
            term_months INTEGER NOT NULL,
            status VARCHAR(255) NOT NULL DEFAULT 'draft' CHECK(status in ({$allowed})),
            rejection_reason TEXT NULL,
            default_stage INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            amount_paid NUMERIC(15, 2) NOT NULL DEFAULT 0,
            outstanding_balance NUMERIC(15, 2) NULL,
            product_id INTEGER NULL,
            interest_method VARCHAR(30) NOT NULL DEFAULT 'REDUCING_BALANCE' CHECK(interest_method in ('REDUCING_BALANCE', 'FLAT')),
            total_repayable NUMERIC NULL,
            monthly_installment NUMERIC NULL,
            outstanding_principal NUMERIC NULL,
            disbursed_at DATE NULL,
            maturity_date DATE NULL,
            applied_by VARCHAR(255) NULL,
            approved_by VARCHAR(255) NULL,
            disbursed_by VARCHAR(255) NULL,
            lien_amount_placed NUMERIC NOT NULL DEFAULT 0,
            FOREIGN KEY(member_id) REFERENCES members(id)
        )");

        DB::statement('INSERT INTO loans_new (
                id, member_id, principal_amount, interest_rate, term_months, status,
                rejection_reason, default_stage, created_at, updated_at, amount_paid, outstanding_balance,
                product_id, interest_method, total_repayable, monthly_installment, outstanding_principal,
                disbursed_at, maturity_date, applied_by, approved_by, disbursed_by, lien_amount_placed
            )
            SELECT id, member_id, principal_amount, interest_rate, term_months, status,
                rejection_reason, COALESCE(default_stage, 0), created_at, updated_at,
                COALESCE(amount_paid, 0), outstanding_balance,
                product_id, COALESCE(interest_method, \'REDUCING_BALANCE\'), total_repayable, monthly_installment, outstanding_principal,
                disbursed_at, maturity_date, applied_by, approved_by, disbursed_by, COALESCE(lien_amount_placed, 0)
            FROM loans');

        DB::statement('DROP TABLE loans');
        // Renaming while UMRA views reference "loans" otherwise triggers schema validation.
        DB::statement('PRAGMA legacy_alter_table = ON');
        DB::statement('ALTER TABLE loans_new RENAME TO loans');
        DB::statement('PRAGMA legacy_alter_table = OFF');
        DB::statement('CREATE INDEX IF NOT EXISTS loans_status_index ON loans (status)');
        DB::statement('CREATE INDEX IF NOT EXISTS loans_member_id_status_index ON loans (member_id, status)');
    }
}
