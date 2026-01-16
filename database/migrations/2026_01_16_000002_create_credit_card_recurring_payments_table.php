<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditCardRecurringPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_card_recurring_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('authorization_id')->constrained('credit_card_recurrence_authorizations')->onDelete('cascade');
            $table->string('transaction_id')->unique();
            $table->string('reference')->nullable();
            $table->decimal('value', 10, 2);
            $table->string('status');
            $table->string('identifier');
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_card_recurring_payments');
    }
}
