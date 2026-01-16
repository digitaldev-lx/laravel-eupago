<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditCardRecurrenceAuthorizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_card_recurrence_authorizations', function (Blueprint $table) {
            $table->id();
            $table->string('subscription_id')->unique();
            $table->string('reference_subs')->nullable();
            $table->text('redirect_url');
            $table->string('status')->default('Pending');
            $table->string('identifier');
            $table->morphs('creditcardrecurrable');
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
        Schema::dropIfExists('credit_card_recurrence_authorizations');
    }
}
