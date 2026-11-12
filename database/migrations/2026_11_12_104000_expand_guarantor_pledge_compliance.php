<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ExpandGuarantorPledgeCompliance extends Migration
{
    public $withinTransaction = false;

    public function up()
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');
        DB::transaction(function () {
            DB::statement("CREATE TABLE guarantor_pledges_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                loan_id INTEGER NOT NULL,
                guarantor_member_id VARCHAR(255) NOT NULL,
                pledged_amount NUMERIC(15, 2) NOT NULL CHECK(pledged_amount > 0),
                guarantee_type VARCHAR(10) NOT NULL DEFAULT 'PARTIAL' CHECK(guarantee_type in ('FULL', 'PARTIAL')),
                amount_enforced NUMERIC(15, 2) NOT NULL DEFAULT 0,
                consent_given TINYINT(1) NOT NULL DEFAULT 0,
                consent_sms_sent TINYINT(1) NOT NULL DEFAULT 0,
                consent_confirmed_at DATETIME NULL,
                guarantor_signature VARCHAR(255) NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'active' CHECK(status in ('active', 'frozen', 'partially_enforced', 'liquidated', 'released')),
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                FOREIGN KEY(loan_id) REFERENCES loans(id) ON DELETE CASCADE,
                FOREIGN KEY(guarantor_member_id) REFERENCES members(id)
            )");

            DB::statement("INSERT INTO guarantor_pledges_new (
                    id, loan_id, guarantor_member_id, pledged_amount, guarantee_type, amount_enforced,
                    consent_given, consent_sms_sent, consent_confirmed_at, guarantor_signature, status, created_at, updated_at
                )
                SELECT id, loan_id, guarantor_member_id, pledged_amount, 'PARTIAL', 0,
                    0, 0, NULL, NULL, status, created_at, updated_at
                FROM guarantor_pledges");

            DB::statement('DROP TABLE guarantor_pledges');
            DB::statement('ALTER TABLE guarantor_pledges_new RENAME TO guarantor_pledges');
        });
        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down()
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');
        DB::transaction(function () {
            DB::statement("CREATE TABLE guarantor_pledges_old (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                loan_id INTEGER NOT NULL,
                guarantor_member_id VARCHAR(255) NOT NULL,
                pledged_amount NUMERIC(15, 2) NOT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'active' CHECK(status in ('active', 'frozen', 'liquidated', 'released')),
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                FOREIGN KEY(loan_id) REFERENCES loans(id) ON DELETE CASCADE,
                FOREIGN KEY(guarantor_member_id) REFERENCES members(id)
            )");

            DB::statement("INSERT INTO guarantor_pledges_old (id, loan_id, guarantor_member_id, pledged_amount, status, created_at, updated_at)
                SELECT id, loan_id, guarantor_member_id, pledged_amount,
                    CASE WHEN status = 'partially_enforced' THEN 'frozen' ELSE status END,
                    created_at, updated_at
                FROM guarantor_pledges");

            DB::statement('DROP TABLE guarantor_pledges');
            DB::statement('ALTER TABLE guarantor_pledges_old RENAME TO guarantor_pledges');
        });
        DB::statement('PRAGMA foreign_keys = ON');
    }
}
