<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\MemberCapitalSnapshot;
use App\Services\MemberCapitalService;
use Illuminate\Console\Command;

class SnapshotMemberCapital extends Command
{
    protected $signature = 'sacco:snapshot-capital';
    protected $description = 'Refreshes member capital and records a dated snapshot for surplus distribution';

    public function handle()
    {
        $count = 0;

        Member::with(['accounts', 'loans', 'guarantorPledges'])->chunk(200, function ($members) use (&$count) {
            foreach ($members as $member) {
                $capital = MemberCapitalService::refresh($member);

                MemberCapitalSnapshot::updateOrCreate(
                    ['member_id' => $member->id, 'snapshot_date' => now()->toDateString()],
                    [
                        'share_balance' => $capital->share_balance,
                        'savings_balance' => $capital->savings_balance,
                    ]
                );

                $count++;
            }
        });

        $this->info("Capital snapshotted for {$count} member(s).");

        return 0;
    }
}
