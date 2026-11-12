<?php

namespace App\Console\Commands;

use App\Services\StandingOrderService;
use Illuminate\Console\Command;

class ProcessStandingOrders extends Command
{
    protected $signature = 'sacco:process-standing-orders {--date= : Process cycles due on or before this date (Y-m-d)}';
    protected $description = 'Posts every due standing order cycle, exactly once per due date';

    public function handle()
    {
        $date = $this->option('date') ? \Carbon\Carbon::parse($this->option('date')) : null;

        $summary = StandingOrderService::processDue($date);

        $this->info(sprintf(
            'Standing orders: %d mandate(s) checked, %d cycle(s) posted for UGX %s, %d cycle(s) failed.',
            $summary['orders'],
            $summary['posted'],
            number_format($summary['amount'], 2),
            $summary['failed']
        ));

        return 0;
    }
}
