<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::firstOrCreate(
    ['email' => 'admin@opensacco.com'],
    [
        'username' => 'Admin User',
        'password' => Hash::make('password123'),
        'role' => 'AC'
    ]
);
echo "User created successfully!\n";
