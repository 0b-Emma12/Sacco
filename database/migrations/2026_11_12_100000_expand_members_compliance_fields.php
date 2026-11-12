<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ExpandMembersComplianceFields extends Migration
{
    public $withinTransaction = false;

    public function up()
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');
        DB::transaction(function () {
            DB::statement("CREATE TABLE members_new (
                id VARCHAR(255) NOT NULL PRIMARY KEY,
                membership_number VARCHAR(20) NOT NULL UNIQUE,
                salutation VARCHAR(255) NOT NULL DEFAULT 'Mr' CHECK(salutation in ('Mr', 'Mrs', 'Ms', 'Dr', 'Prof', 'Rev')),
                first_name VARCHAR(100) NOT NULL,
                middle_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                national_id VARCHAR(20) NOT NULL UNIQUE,
                phone_number VARCHAR(20) NOT NULL,
                email VARCHAR(255) NULL,
                date_of_birth DATE NOT NULL,
                gender VARCHAR(10) NULL CHECK(gender in ('MALE', 'FEMALE', 'OTHER')),
                kra_pin VARCHAR(100) NOT NULL,
                country VARCHAR(100) NOT NULL,
                county VARCHAR(100) NOT NULL,
                city VARCHAR(100) NOT NULL,
                common_bond_type VARCHAR(20) NULL CHECK(common_bond_type in ('EMPLOYER', 'PROFESSION', 'COMMUNITY', 'CHAMA')),
                common_bond_value VARCHAR(255) NULL,
                status VARCHAR(255) NOT NULL DEFAULT 'Active' CHECK(status in ('Active', 'Closed', 'Dormant', 'Suspended', 'Pending', 'incubating', 'Exited', 'Deceased')),
                kyc_verified TINYINT(1) NOT NULL DEFAULT 0,
                total_savings NUMERIC(15, 2) NOT NULL DEFAULT 0,
                share_capital NUMERIC(15, 2) NOT NULL DEFAULT 0,
                credit_limit NUMERIC(15, 2) NOT NULL DEFAULT 0,
                date_joined DATE NOT NULL,
                exit_requested_at DATETIME NULL,
                notice_period_end DATE NULL,
                exit_date DATE NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )");

            DB::statement('INSERT INTO members_new (
                    id, membership_number, salutation, first_name, middle_name, last_name,
                    national_id, phone_number, email, date_of_birth, kra_pin, country, county, city,
                    status, total_savings, share_capital, credit_limit, date_joined, created_at, updated_at
                )
                SELECT id, membership_number, salutation, first_name, middle_name, last_name,
                    national_id, phone_number, email, date_of_birth, kra_pin, country, county, city,
                    status, total_savings, share_capital, credit_limit, date_joined, created_at, updated_at
                FROM members');

            DB::statement('DROP TABLE members');
            DB::statement('ALTER TABLE members_new RENAME TO members');
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
            DB::statement("CREATE TABLE members_old (
                id VARCHAR(255) NOT NULL PRIMARY KEY,
                membership_number VARCHAR(20) NOT NULL UNIQUE,
                salutation VARCHAR(255) NOT NULL DEFAULT 'Mr' CHECK(salutation in ('Mr', 'Mrs', 'Ms', 'Dr', 'Prof', 'Rev')),
                first_name VARCHAR(100) NOT NULL,
                middle_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                national_id VARCHAR(20) NOT NULL UNIQUE,
                phone_number VARCHAR(20) NOT NULL,
                email VARCHAR(255) NULL,
                date_of_birth DATE NOT NULL,
                kra_pin VARCHAR(100) NOT NULL,
                country VARCHAR(100) NOT NULL,
                county VARCHAR(100) NOT NULL,
                city VARCHAR(100) NOT NULL,
                status VARCHAR(255) NOT NULL DEFAULT 'Active' CHECK(status in ('Active', 'Closed', 'Dormant', 'Suspended', 'Pending', 'incubating')),
                total_savings NUMERIC(15, 2) NOT NULL DEFAULT 0,
                share_capital NUMERIC(15, 2) NOT NULL DEFAULT 0,
                credit_limit NUMERIC(15, 2) NOT NULL DEFAULT 0,
                date_joined DATE NOT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )");

            DB::statement("INSERT INTO members_old (
                    id, membership_number, salutation, first_name, middle_name, last_name,
                    national_id, phone_number, email, date_of_birth, kra_pin, country, county, city,
                    status, total_savings, share_capital, credit_limit, date_joined, created_at, updated_at
                )
                SELECT id, membership_number, salutation, first_name, middle_name, last_name,
                    national_id, phone_number, email, date_of_birth, kra_pin, country, county, city,
                    CASE WHEN status IN ('Exited', 'Deceased') THEN 'Closed' ELSE status END,
                    total_savings, share_capital, credit_limit, date_joined, created_at, updated_at
                FROM members");

            DB::statement('DROP TABLE members');
            DB::statement('ALTER TABLE members_old RENAME TO members');
        });
        DB::statement('PRAGMA foreign_keys = ON');
    }
}
