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
        Schema::create('client_account', function (Blueprint $table) {
            $table->id();
            
            $table->string('solde');
            $table->string('iban');
            $table->string('bic');
            $table->boolean('account_activated')->default(false); 
            $table->unsignedBigInteger('client_id'); 
            $table->foreign('client_id')->references('id')->on('clients');   
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_account');
    }
};
