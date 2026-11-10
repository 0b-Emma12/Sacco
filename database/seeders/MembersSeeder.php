<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use Illuminate\Support\Str;

class MembersSeeder extends Seeder
{
    public function run()
    {
        $alice = Member::firstOrCreate(
            ['national_id' => 'NID12345'],
            [
                'id' => (string) Str::uuid(),
                'membership_number' => 'MBR-001',
                'first_name' => 'Alice',
                'middle_name' => 'W',
                'last_name' => 'Smith',
                'phone_number' => '0770000001',
                'date_of_birth' => '1990-01-01',
                'kra_pin' => 'A123456789Z',
                'country' => 'Uganda',
                'county' => 'Kampala',
                'city' => 'Kampala',
                'date_joined' => now(),
                'status' => 'Active',
                'total_savings' => 50000,
            ]
        );

        $bob = Member::firstOrCreate(
            ['national_id' => 'NID98765'],
            [
                'id' => (string) Str::uuid(),
                'membership_number' => 'MBR-002',
                'first_name' => 'Bob',
                'middle_name' => 'T',
                'last_name' => 'Jones',
                'phone_number' => '0770000002',
                'date_of_birth' => '1992-05-05',
                'kra_pin' => 'A987654321Z',
                'country' => 'Uganda',
                'county' => 'Kampala',
                'city' => 'Kampala',
                'date_joined' => now(),
                'status' => 'Active',
                'total_savings' => 10000,
            ]
        );

        $savingsProduct = \App\Models\Product::firstOrCreate(
            ['name' => 'Regular Savings'],
            ['description' => 'Standard member savings account']
        );

        \App\Models\Account::firstOrCreate(
            ['account_number' => 'ACC-001'],
            ['member_id' => $alice->id, 'product_id' => $savingsProduct->id, 'balance' => 50000]
        );

        \App\Models\Account::firstOrCreate(
            ['account_number' => 'ACC-002'],
            ['member_id' => $bob->id, 'product_id' => $savingsProduct->id, 'balance' => 10000]
        );
    }
}
