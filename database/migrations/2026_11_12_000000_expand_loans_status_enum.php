<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ExpandLoansStatusEnum extends Migration
{
    public $withinTransaction = false;

    private const STATUSES = [
        'draft', 'submitted', 'under_review', 'approved', 'rejected',
        'disbursed', 'partially_paid', 'paid', 'defaulted', 'closed',
    ];

    public function up()
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE loans MODIFY status ENUM('draft','submitted','under_review','approved','rejected','disbursed','partially_paid','paid','defaulted','closed') NOT NULL DEFAULT 'draft'");
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');
        DB::transaction(fn () => $this->rebuild(self::STATUSES));
        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down()
    {
        $legacy = ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'disbursed', 'defaulted', 'closed'];

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE loans MODIFY status ENUM('draft','submitted','under_review','approved','rejected','disbursed','defaulted','closed') NOT NULL DEFAULT 'draft'");
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');
        DB::transaction(function () use ($legacy) {
            DB::table('loans')->whereIn('status', ['partially_paid', 'paid'])->update(['status' => 'disbursed']);
            $this->rebuild($legacy);
        });
        DB::statement('PRAGMA foreign_keys = ON');
    }

    private function rebuild(array $statuses): void
    {
        $allowed = collect($statuses)->map(fn ($status) => "'".$status."'")->implode(', ');

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
            FOREIGN KEY(member_id) REFERENCES members(id)
        )");

        DB::statement('INSERT INTO loans_new (
                id, member_id, principal_amount, interest_rate, term_months, status,
                rejection_reason, default_stage, created_at, updated_at, amount_paid, outstanding_balance
            )
            SELECT id, member_id, principal_amount, interest_rate, term_months, status,
                rejection_reason, COALESCE(default_stage, 0), created_at, updated_at,
                COALESCE(amount_paid, 0), outstanding_balance
            FROM loans');

        DB::statement('DROP TABLE loans');
        DB::statement('ALTER TABLE loans_new RENAME TO loans');
    }
}
