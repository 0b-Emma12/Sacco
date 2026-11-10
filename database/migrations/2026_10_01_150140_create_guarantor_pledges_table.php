<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuarantorPledgesTable extends Migration
{
    public function up()
    {
        Schema::create('guarantor_pledges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('guarantor_member_id')->constrained('members');
            $table->decimal('pledged_amount', 15, 2);
            $table->enum('status', ['active', 'frozen', 'liquidated', 'released'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('guarantor_pledges');
    }
}
