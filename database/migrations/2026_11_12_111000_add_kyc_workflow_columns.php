<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddKycWorkflowColumns extends Migration
{
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('kyc_status', 20)->default('PENDING');
            $table->dateTime('kyc_verified_at')->nullable();
            $table->string('kyc_verified_by')->nullable();
            $table->text('kyc_rejection_reason')->nullable();
            $table->date('kyc_next_review_at')->nullable();
            $table->text('kyc_documents')->nullable();
            $table->text('kyc_notes')->nullable();

            $table->index('kyc_status');
            $table->index('kyc_next_review_at');
        });

        DB::table('members')->where('kyc_verified', 1)->update([
            'kyc_status' => 'VERIFIED',
            'kyc_verified_at' => DB::raw('COALESCE(updated_at, created_at)'),
            'kyc_next_review_at' => DB::raw("date(COALESCE(updated_at, created_at), '+2 years')"),
        ]);
    }

    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex(['kyc_status']);
            $table->dropIndex(['kyc_next_review_at']);
            $table->dropColumn([
                'kyc_status',
                'kyc_verified_at',
                'kyc_verified_by',
                'kyc_rejection_reason',
                'kyc_next_review_at',
                'kyc_documents',
                'kyc_notes',
            ]);
        });
    }
}
