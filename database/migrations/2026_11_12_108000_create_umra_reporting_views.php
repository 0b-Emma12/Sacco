<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateUmraReportingViews extends Migration
{
    public function up()
    {
        DB::statement('DROP VIEW IF EXISTS v_portfolio_ageing');
        DB::statement('DROP VIEW IF EXISTS v_capital_adequacy');
        DB::statement('DROP VIEW IF EXISTS v_liquidity');
        DB::statement('DROP VIEW IF EXISTS v_par');
        DB::statement('DROP VIEW IF EXISTS v_loan_arrears');

        DB::statement("CREATE VIEW v_loan_arrears AS
            SELECT l.id AS loan_id, l.member_id, l.status,
                COALESCE(SUM(CASE WHEN rs.payment_status NOT IN ('PAID', 'WAIVED') AND rs.due_date < date('now')
                    THEN (rs.total_due - rs.total_paid) ELSE 0 END), 0) AS overdue_amount,
                COALESCE(MAX(CASE WHEN rs.payment_status NOT IN ('PAID', 'WAIVED') AND rs.due_date < date('now')
                    THEN CAST(julianday(date('now')) - julianday(rs.due_date) AS INTEGER) ELSE 0 END), 0) AS days_overdue
            FROM loans l
            LEFT JOIN repayment_schedules rs ON rs.loan_id = l.id
            WHERE l.status IN ('disbursed', 'partially_paid', 'defaulted')
            GROUP BY l.id, l.member_id, l.status");

        DB::statement("CREATE VIEW v_par AS
            SELECT
                COALESCE((SELECT SUM(principal_amount) FROM loans WHERE status IN ('disbursed', 'partially_paid', 'defaulted')), 0) AS gross_portfolio,
                COALESCE((SELECT SUM(overdue_amount) FROM v_loan_arrears), 0) AS total_arrears,
                COALESCE((SELECT SUM(overdue_amount) FROM v_loan_arrears WHERE days_overdue > 30), 0) AS par30_amount,
                CASE WHEN COALESCE((SELECT SUM(principal_amount) FROM loans WHERE status IN ('disbursed', 'partially_paid', 'defaulted')), 0) > 0
                    THEN COALESCE((SELECT SUM(overdue_amount) FROM v_loan_arrears WHERE days_overdue > 30), 0) * 100.0
                         / (SELECT SUM(principal_amount) FROM loans WHERE status IN ('disbursed', 'partially_paid', 'defaulted'))
                    ELSE 0 END AS par30_ratio");

        DB::statement("CREATE VIEW v_liquidity AS
            SELECT
                COALESCE((SELECT SUM(jel.debit - jel.credit) FROM journal_entry_lines jel WHERE jel.account_code IN ('1000', '1300')), 0) AS liquid_assets,
                COALESCE((SELECT SUM(jel.credit - jel.debit) FROM journal_entry_lines jel WHERE jel.account_code = '2000'), 0) AS total_deposits,
                CASE WHEN COALESCE((SELECT SUM(jel.credit - jel.debit) FROM journal_entry_lines jel WHERE jel.account_code = '2000'), 0) > 0
                    THEN COALESCE((SELECT SUM(jel.debit - jel.credit) FROM journal_entry_lines jel WHERE jel.account_code IN ('1000', '1300')), 0) * 100.0
                         / (SELECT SUM(jel.credit - jel.debit) FROM journal_entry_lines jel WHERE jel.account_code = '2000')
                    ELSE 0 END AS liquidity_ratio");

        DB::statement("CREATE VIEW v_capital_adequacy AS
            SELECT
                COALESCE((SELECT SUM(jel.credit - jel.debit) FROM journal_entry_lines jel WHERE jel.account_code IN ('3000', '3100')), 0) AS core_capital,
                COALESCE((SELECT SUM(jel.debit - jel.credit) FROM journal_entry_lines jel WHERE jel.account_code = '1200'), 0) AS risk_weighted_assets,
                CASE WHEN COALESCE((SELECT SUM(jel.debit - jel.credit) FROM journal_entry_lines jel WHERE jel.account_code = '1200'), 0) > 0
                    THEN COALESCE((SELECT SUM(jel.credit - jel.debit) FROM journal_entry_lines jel WHERE jel.account_code IN ('3000', '3100')), 0) * 100.0
                         / (SELECT SUM(jel.debit - jel.credit) FROM journal_entry_lines jel WHERE jel.account_code = '1200')
                    ELSE 0 END AS capital_adequacy_ratio");

        DB::statement("CREATE VIEW v_portfolio_ageing AS
            SELECT
                COALESCE(SUM(CASE WHEN days_overdue BETWEEN 1 AND 30 THEN overdue_amount ELSE 0 END), 0) AS bucket_1_30,
                COALESCE(SUM(CASE WHEN days_overdue BETWEEN 31 AND 90 THEN overdue_amount ELSE 0 END), 0) AS bucket_31_90,
                COALESCE(SUM(CASE WHEN days_overdue BETWEEN 91 AND 180 THEN overdue_amount ELSE 0 END), 0) AS bucket_91_180,
                COALESCE(SUM(CASE WHEN days_overdue BETWEEN 181 AND 365 THEN overdue_amount ELSE 0 END), 0) AS bucket_181_365,
                COALESCE(SUM(CASE WHEN days_overdue > 365 THEN overdue_amount ELSE 0 END), 0) AS bucket_365_plus
            FROM v_loan_arrears");
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS v_portfolio_ageing');
        DB::statement('DROP VIEW IF EXISTS v_capital_adequacy');
        DB::statement('DROP VIEW IF EXISTS v_liquidity');
        DB::statement('DROP VIEW IF EXISTS v_par');
        DB::statement('DROP VIEW IF EXISTS v_loan_arrears');
    }
}
