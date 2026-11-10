<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('last_value')->default(0);
        });

        Schema::create('members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('membership_number', 20)->unique();
            
            $table->enum('salutation', ['Mr', 'Mrs', 'Ms', 'Dr', 'Prof', 'Rev'])->default('Mr');
            $table->string('first_name', 100);
            $table->string('middle_name', 100);
            $table->string('last_name', 100);
            $table->string('national_id', 20)->unique();
            $table->string('phone_number', 20);
            $table->string('email')->nullable();
            $table->date('date_of_birth');
            $table->string('kra_pin', 100);
            
            $table->string('country', 100);
            $table->string('county', 100);
            $table->string('city', 100);
            
            $table->enum('status', ['Active', 'Closed', 'Dormant', 'Suspended', 'Pending', 'incubating'])->default('Active');
            
            $table->decimal('total_savings', 15, 2)->default(0);
            $table->decimal('share_capital', 15, 2)->default(0);
            $table->decimal('credit_limit', 15, 2)->default(0);
            
            $table->date('date_joined');
            $table->timestamps();
        });

        Schema::create('next_of_kins', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('member_id')->constrained('members')->onDelete('cascade');
            $table->string('name', 200);
            $table->string('relationship', 100);
            $table->string('phone_number', 20);
            $table->string('national_id', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('employment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('member_id')->constrained('members')->onDelete('cascade');
            $table->enum('employment_type', ['EMPLOYED', 'SELF_EMPLOYED', 'BUSINESS', 'UNEMPLOYED']);
            $table->string('employer_name')->nullable();
            $table->string('job_title')->nullable();
            $table->decimal('monthly_income', 12, 2)->nullable();
            $table->string('business_name')->nullable();
            $table->string('business_type')->nullable();
            $table->timestamps();
        });

        Schema::create('kyc_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('member_id')->constrained('members')->onDelete('cascade');
            $table->enum('document_type', ['NATIONAL_ID', 'PASSPORT_PHOTO', 'SIGNATURE']);
            $table->string('file');
            $table->boolean('verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_documents');
        Schema::dropIfExists('employment_details');
        Schema::dropIfExists('next_of_kins');
        Schema::dropIfExists('members');
        Schema::dropIfExists('member_sequences');
    }
};
