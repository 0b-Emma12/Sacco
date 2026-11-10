<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CalculateDividends extends Command
{
    protected $signature = 'sacco:calculate-dividends {pool : The total net surplus/dividend pool to distribute (in UGX)}';
    protected $description = 'Calculates and credits annual dividends to members based on their proportional share capital';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $pool = (float) $this->argument('pool');
        
        if ($pool <= 0) {
            $this->error("The dividend pool must be greater than zero.");
            return 1;
        }

        $totalShareCapital = Member::where('status', 'Active')->sum('share_capital');

        if ($totalShareCapital <= 0) {
            $this->error("Total active share capital is zero. Cannot distribute dividends.");
            return 1;
        }

        $this->info("Total Active Share Capital: UGX " . number_format($totalShareCapital, 2));
        $this->info("Declared Dividend Pool: UGX " . number_format($pool, 2));
        $this->info("Calculating proportional payouts...");

        $members = Member::where('status', 'Active')->where('share_capital', '>', 0)->get();
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($members as $member) {
                // Calculate proportional share
                $sharePercentage = $member->share_capital / $totalShareCapital;
                $dividendAmount = $pool * $sharePercentage;

                if ($dividendAmount > 0) {
                    $account = $member->accounts()->first(); // Credit to their primary account
                    
                    if ($account) {
                        $account->balance += $dividendAmount;
                        $account->save();

                        $member->total_savings += $dividendAmount;
                        $member->save();

                        Transaction::create([
                            'reference' => 'DIV-'.Str::random(6),
                            'account_number' => $account->account_number,
                            'transaction_type' => 'deposit',
                            'amount' => $dividendAmount,
                            'narration' => 'Annual Dividend Payout (Share Capital: UGX '.number_format($member->share_capital).')',
                            'performed_by_username' => 'System',
                        ]);

                        $count++;
                    }
                }
            }
            DB::commit();
            $this->info("Successfully credited dividends to {$count} members.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("An error occurred during dividend distribution: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
