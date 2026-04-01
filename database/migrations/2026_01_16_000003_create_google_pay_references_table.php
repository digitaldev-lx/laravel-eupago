<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGooglePayReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_pay_references', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('reference')->nullable();
            $table->decimal('value', 10, 2);
            $table->text('redirect_url');
            $table->integer('state')->default(0);
            $table->string('callback_transaction_id')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_first_name')->nullable();
            $table->string('customer_last_name')->nullable();
            $table->string('customer_country_code')->nullable();
            $table->morphs('googlepayable');
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
        Schema::dropIfExists('google_pay_references');
    }
}
