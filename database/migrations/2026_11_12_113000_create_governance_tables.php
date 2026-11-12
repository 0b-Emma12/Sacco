<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGovernanceTables extends Migration
{
    public function up()
    {
        Schema::create('governance_meetings', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10)->default('BOARD');
            $table->string('title');
            $table->date('meeting_date');
            $table->string('venue')->nullable();
            $table->text('agenda')->nullable();
            $table->text('attendance')->nullable();
            $table->text('absent_with_apology')->nullable();
            $table->text('resolutions')->nullable();
            $table->text('action_items')->nullable();
            $table->boolean('quorum_met')->default(false);
            $table->unsignedInteger('members_present')->nullable();
            $table->unsignedInteger('members_total')->nullable();
            $table->decimal('dividend_on_shares', 5, 2)->nullable();
            $table->decimal('interest_on_savings', 5, 2)->nullable();
            $table->string('status', 20)->default('DRAFT');
            $table->string('prepared_by');
            $table->string('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index('meeting_date');
        });

        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('version', 30)->default('1.0');
            $table->date('effective_date')->nullable();
            $table->text('summary');
            $table->text('change_reason')->nullable();
            $table->text('impact_assessment')->nullable();
            $table->string('status', 20)->default('DRAFT');
            $table->string('resolution_number')->nullable();
            $table->string('created_by');
            $table->string('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('policies');
        Schema::dropIfExists('governance_meetings');
    }
}
