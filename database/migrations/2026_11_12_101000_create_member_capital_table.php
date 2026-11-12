<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMemberCapitalTable extends Migration
{
    public function up()
    {
        Schema::create('member_capital', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('member_id')->unique()->constrained('members')->cascadeOnDelete();
            $table->decimal('share_count', 15, 4)->default(0);
            $table->decimal('share_balance', 15, 2)->default(0);
            $table->decimal('savings_balance', 15, 2)->default(0);
            $table->decimal('mandatory_savings', 15, 2)->default(0);
            $table->decimal('voluntary_savings', 15, 2)->default(0);
            $table->decimal('lien_amount', 15, 2)->default(0);
            $table->decimal('guarantee_liability', 15, 2)->default(0);
            $table->decimal('available_savings', 15, 2)->default(0);
            $table->decimal('total_equity', 15, 2)->default(0);
            $table->timestamps();
        });

        $sharePrice = (float) (DB::table('settings')->where('key', 'sys_share_price')->value('value') ?: 10000);

        foreach (DB::table('members')->get() as $member) {
            $savings = (float) $member->total_savings;
            $shares = (float) $member->share_capital;
            $guarantees = (float) DB::table('guarantor_pledges')
                ->where('guarantor_member_id', $member->id)
                ->whereIn('status', ['active', 'frozen'])
                ->sum('pledged_amount');
            $lien = (float) DB::table('loans')
                ->where('member_id', $member->id)
                ->whereIn('status', ['approved', 'disbursed', 'partially_paid', 'defaulted'])
                ->sum('principal_amount');

            DB::table('member_capital')->insert([
                'member_id' => $member->id,
                'share_count' => $sharePrice > 0 ? round($shares / $sharePrice, 4) : 0,
                'share_balance' => $shares,
                'savings_balance' => $savings,
                'mandatory_savings' => 0,
                'voluntary_savings' => $savings,
                'lien_amount' => min($savings, $lien),
                'guarantee_liability' => $guarantees,
                'available_savings' => max(0, $savings - min($savings, $lien) - $guarantees),
                'total_equity' => $savings + $shares,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('member_capital');
    }
}
