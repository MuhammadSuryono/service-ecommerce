<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipperTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipper', function (Blueprint $table) {
            $table->id();
            $table->string("id_order");
            $table->string("recipents_name");
            $table->string("address");
            $table->string("contact");
            $table->string("order_notes");
            $table->string("destination");
            $table->integer("cost");
            $table->integer("destination_id");
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
        Schema::dropIfExists('shipper');
    }
}
