<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->string('transaction_description');
            $table->string('beneficiary_name');
            $table->string('beneficiary_iban');
            $table->unsignedBigInteger('client_account_id');
            $table->foreign('client_account_id')->references('id')->on('client_account');
            $table->string('transaction_type')->required();
            $table->string('transaction_amount');
            $table->string('transaction_status')->default('pending');
            $table->string('transaction_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
