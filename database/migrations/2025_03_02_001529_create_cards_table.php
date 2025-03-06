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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->date('date_validity');
            $table->date('date_expiration'); 
            $table->string('card_number')->unique();
            $table->string('cvv');
            $table->boolean('card_activated')->default(true); 
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
        Schema::dropIfExists('cards');
    }
};
