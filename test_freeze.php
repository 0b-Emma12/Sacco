<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Member;
use App\Models\Loan;
use App\Models\GuarantorPledge;
use Illuminate\Support\Str;

// 1. Create a guarantor with 50,000 total savings
$guarantor = Member::create([
    'id' => (string) Str::uuid(),
    'membership_number' => 'MBR-001',
    'first_name' => 'Alice',
    'middle_name' => 'W',
    'last_name' => 'Smith',
    'national_id' => 'NID12345',
    'phone_number' => '0770000001',
    'date_of_birth' => '1990-01-01',
    'kra_pin' => 'A123456789Z',
    'country' => 'Uganda',
    'county' => 'Kampala',
    'city' => 'Kampala',
    'date_joined' => now(),
    'status' => 'Active',
    'total_savings' => 50000,
]);

// 2. Create a borrower
$borrower = Member::create([
    'id' => (string) Str::uuid(),
    'membership_number' => 'MBR-002',
    'first_name' => 'Bob',
    'middle_name' => 'T',
    'last_name' => 'Jones',
    'national_id' => 'NID98765',
    'phone_number' => '0770000002',
    'date_of_birth' => '1992-05-05',
    'kra_pin' => 'A987654321Z',
    'country' => 'Uganda',
    'county' => 'Kampala',
    'city' => 'Kampala',
    'date_joined' => now(),
    'status' => 'Active',
    'total_savings' => 10000,
]);

// 3. Create a loan for Bob
$loan = Loan::create([
    'member_id' => $borrower->id,
    'principal_amount' => 15000,
    'interest_rate' => 5.0,
    'term_months' => 6,
    'status' => 'approved',
]);

// 4. Alice pledges 20,000 as a guarantor for Bob's loan (Frozen)
GuarantorPledge::create([
    'loan_id' => $loan->id,
    'guarantor_member_id' => $guarantor->id,
    'pledged_amount' => 20000,
    'status' => 'frozen',
]);

// 5. Output Alice's financial state
echo "--- Alice's Account ---\n";
echo "Total Savings: " . $guarantor->total_savings . "\n";
echo "Available Savings (After Pledge): " . $guarantor->available_savings . "\n";
