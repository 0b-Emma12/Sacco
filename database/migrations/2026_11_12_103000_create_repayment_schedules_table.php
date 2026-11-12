<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepaymentSchedulesTable extends Migration
{
    public function up()
    {
        Schema::create('repayment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('installment_number');
            $table->date('due_date');
            $table->decimal('principal_due', 15, 2);
            $table->decimal('interest_due', 15, 2);
            $table->decimal('total_due', 15, 2);
            $table->decimal('principal_paid', 15, 2)->default(0);
            $table->decimal('interest_paid', 15, 2)->default(0);
            $table->decimal('total_paid', 15, 2)->default(0);
            $table->enum('payment_status', ['PENDING', 'PARTIAL', 'PAID', 'OVERDUE', 'WAIVED'])->default('PENDING');
            $table->integer('days_overdue')->default(0);
            $table->decimal('penalty_charged', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['loan_id', 'installment_number']);
            $table->index(['due_date', 'payment_status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('repayment_schedules');
    }
}
