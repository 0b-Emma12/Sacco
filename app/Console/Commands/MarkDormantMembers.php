<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MarkDormantMembers extends Command
{
    protected $signature = 'sacco:mark-dormant {--days= : Inactivity window in days}';
    protected $description = 'Flags active members with no transactions and no loans as dormant';

    public function handle()
    {
        $days = (int) ($this->option('days') ?: Setting::get('member_dormancy_days', 90));
        $cutoff = Carbon::now()->subDays($days);

        $recentlyActive = DB::table('accounts')
            ->join('transactions', 'transactions.account_number', '=', 'accounts.account_number')
            ->where('transactions.created_at', '>=', $cutoff)
            ->pluck('accounts.member_id')
            ->unique()
            ->all();

        $candidates = Member::where('status', 'Active')
            ->where('date_joined', '<', $cutoff)
            ->whereNotIn('id', $recentlyActive)
            ->whereDoesntHave('loans', function ($q) {
                $q->whereIn('status', ['disbursed', 'partially_paid', 'defaulted']);
            })
            ->get();

        foreach ($candidates as $member) {
            $member->update(['status' => 'Dormant']);
            $this->info("Member {$member->membership_number} marked dormant.");
        }

        $this->info($candidates->count()." member(s) marked dormant after {$days} days of inactivity.");

        return 0;
    }
}
