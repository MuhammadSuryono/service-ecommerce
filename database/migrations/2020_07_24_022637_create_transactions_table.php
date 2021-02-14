<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->integer('transaction_id');
            $table->string('payment_type');
            $table->string('total');
            $table->string("time_create_payment");
            $table->string('transaction_time');
            $table->string('transaction_status');
            $table->string('status_pengiriman');
            $table->string('number_resi')->default('0');
            $table->text('detail_transactions');
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
        Schema::dropIfExists('transactions');
    }
}
