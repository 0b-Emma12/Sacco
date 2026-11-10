<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CheckDefaults extends Command
{
    protected $signature = 'sacco:check-defaults {--mock-days= : Force the number of days overdue for testing}';
    protected $description = 'Checks for defaulted loans and executes automated recovery protocols';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info("Starting Defaulted Loans Check...");

        $loans = \App\Models\Loan::whereIn('status', ['disbursed', 'defaulted'])->get();

        foreach ($loans as $loan) {
            // For production, this would calculate exact days overdue based on a RepaymentSchedules table.
            // For MVP/testing, we calculate based on the loan's term or use a mock parameter.
            $expectedEndDate = $loan->updated_at->copy()->addMonths($loan->term_months);
            $daysOverdue = now()->diffInDays($expectedEndDate, false) * -1; 
            
            if ($this->option('mock-days')) {
                $daysOverdue = (int) $this->option('mock-days');
            }

            if ($daysOverdue > 90) {
                $this->handleStage4($loan);
            } elseif ($daysOverdue > 60) {
                $this->handleStage3($loan);
            } elseif ($daysOverdue > 30) {
                $this->handleStage2($loan);
            } elseif ($daysOverdue > 0) {
                $this->handleStage1($loan);
            }
        }

        $this->info("Completed Defaulted Loans Check.");
        return 0;
    }

    private function handleStage1($loan) {
        if ($loan->default_stage < 1) {
            $loan->update(['status' => 'defaulted', 'default_stage' => 1]);
            $this->info("Loan {$loan->id} entered Stage 1. Sending SMS to defaulter and guarantors.");
            // Logic to trigger SMS notification would go here
        }
    }

    private function handleStage2($loan) {
        if ($loan->default_stage < 2) {
            $loan->update(['status' => 'defaulted', 'default_stage' => 2]);
            $this->info("Loan {$loan->id} entered Stage 2. Liquidating defaulter's own savings.");
            
            $member = $loan->member;
            $outstandingBalance = $this->getOutstandingBalance($loan);
            
            // We ensure we don't deduct more than they actually have in total savings
            if ($member->total_savings > 0 && $outstandingBalance > 0) {
                $deduction = min($member->total_savings, $outstandingBalance);
                $member->total_savings -= $deduction;
                $member->save();
                
                $account = $member->accounts()->first();
                if ($account) {
                    $account->balance -= $deduction;
                    $account->save();
                    
                    \App\Models\Transaction::create([
                        'reference' => 'LIQ-'.Str::random(6),
                        'account_number' => $account->account_number,
                        'transaction_type' => 'withdrawal',
                        'amount' => $deduction,
                        'narration' => 'Automated Stage 2 Default Liquidation for Loan APP-'.$loan->id,
                        'performed_by_username' => 'System',
                    ]);
                }
            }
        }
    }

    private function handleStage3($loan) {
        if ($loan->default_stage < 3) {
            $loan->update(['status' => 'defaulted', 'default_stage' => 3]);
            $this->info("Loan {$loan->id} entered Stage 3. Liquidating guarantor pledges.");
            
            $outstandingBalance = $this->getOutstandingBalance($loan);
            
            foreach ($loan->guarantorPledges()->where('status', 'frozen')->get() as $pledge) {
                if ($outstandingBalance <= 0) break;

                $guarantor = $pledge->guarantor;
                $deduction = min($pledge->pledged_amount, $outstandingBalance);
                
                $guarantor->total_savings -= $deduction;
                $guarantor->save();
                
                $gAccount = $guarantor->accounts()->first();
                if ($gAccount) {
                    $gAccount->balance -= $deduction;
                    $gAccount->save();
                    
                    \App\Models\Transaction::create([
                        'reference' => 'GLIQ-'.Str::random(6),
                        'account_number' => $gAccount->account_number,
                        'transaction_type' => 'withdrawal',
                        'amount' => $deduction,
                        'narration' => 'Automated Stage 3 Guarantor Liquidation for Loan APP-'.$loan->id,
                        'performed_by_username' => 'System',
                    ]);
                }
                
                // Update pledge status to liquidated
                $pledge->update(['status' => 'liquidated']);
                $outstandingBalance -= $deduction;
            }
        }
    }

    private function handleStage4($loan) {
        if ($loan->default_stage < 4) {
            $loan->update(['status' => 'defaulted', 'default_stage' => 4]);
            $this->info("Loan {$loan->id} entered Stage 4. Flagged for external debt collection.");
        }
    }

    private function getOutstandingBalance($loan) {
        $p = $loan->principal_amount;
        $r = $loan->interest_rate / 100;
        $n = $loan->term_months;
        
        if ($r > 0 && $n > 0) {
            $emi = $p * $r * pow(1 + $r, $n) / (pow(1 + $r, $n) - 1);
            return $emi * $n;
        }
        
        return $p;
    }
}
