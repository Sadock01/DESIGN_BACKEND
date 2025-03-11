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
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('BIC_code', 11)->unique(); // BIC est généralement de 8 ou 11 caractères
            $table->string('IBAN_code', 34)->unique(); // IBAN max 34 caractères
            $table->string('account_number')->unique();
            $table->unsignedBigInteger('client_account_id');
            $table->foreign('client_account_id')->references('id')->on('client_account'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('benificiares');
    }
};
