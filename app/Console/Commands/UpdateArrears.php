<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Services\RepaymentScheduleService;
use Illuminate\Console\Command;

class UpdateArrears extends Command
{
    protected $signature = 'sacco:update-arrears';
    protected $description = 'Refreshes repayment schedule ageing, arrears and PAR for all active loans';

    public function handle()
    {
        $loans = Loan::whereIn('status', ['disbursed', 'partially_paid', 'defaulted'])->get();
        $updated = 0;
        $defaulted = 0;

        foreach ($loans as $loan) {
            if (! $loan->schedules()->exists()) {
                RepaymentScheduleService::generate($loan);
            }

            $days = RepaymentScheduleService::refreshOverdue($loan);

            if ($days > 90 && $loan->status !== 'defaulted') {
                $loan->update(['status' => 'defaulted']);
                $defaulted++;
            } elseif ($days > 0 && $loan->status === 'disbursed' && (float) $loan->amount_paid > 0) {
                $loan->update(['status' => 'partially_paid']);
            }

            $updated++;
        }

        $this->info("Arrears refreshed for {$updated} loan(s); {$defaulted} loan(s) moved to default.");

        return 0;
    }
}
