<?php
$db = new PDO('sqlite:database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("PRAGMA foreign_keys=OFF;");
$db->exec("ALTER TABLE loans RENAME TO loans_old;");
$db->exec("
CREATE TABLE loans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id VARCHAR(255) NOT NULL,
    principal_amount NUMERIC(15, 2) NOT NULL,
    interest_rate NUMERIC(5, 2) NOT NULL,
    term_months INTEGER NOT NULL,
    status VARCHAR(255) DEFAULT 'draft' CHECK(status in ('draft', 'submitted', 'under_review', 'approved', 'disbursed', 'defaulted', 'closed', 'rejected')),
    default_stage INTEGER DEFAULT 0 NOT NULL,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY(member_id) REFERENCES members(id)
);
");
$db->exec("INSERT INTO loans SELECT * FROM loans_old;");
$db->exec("DROP TABLE loans_old;");
$db->exec("PRAGMA foreign_keys=ON;");
echo "Updated loans table enum constraint.\n";
