<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrintLogsTable extends Migration
{
    public function up()
    {
        Schema::create('print_logs', function (Blueprint $table) {
            $table->id();
            $table->string('document_id')->unique();
            $table->string('document_type', 40)->index();
            $table->string('subject_type', 60)->nullable();
            $table->string('subject_id', 60)->nullable();
            $table->string('member_id', 60)->nullable()->index();
            $table->unsignedBigInteger('printed_by_id')->nullable();
            $table->string('printed_by_name')->nullable();
            $table->string('watermark', 20)->default('ORIGINAL');
            $table->boolean('is_reprint')->default(false);
            $table->string('verification_token', 64)->unique();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('print_logs');
    }
}
