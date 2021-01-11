<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdmTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdm', function (Blueprint $table) {
            $table->increments('id')->start_from(180000);
            $table->integer('user_id')->unsigned();
            $table->string('name', 30);
            $table->string('gender', 30);
            $table->integer('phone');
            $table->string('sate', 30);
            $table->string('distric', 30);
            $table->string('panno', 30);
            $table->date('dob');
            $table->string('email', 30);
            $table->integer('pin');
            $table->string('adhno', 30);
            $table->string('bank', 30);
            $table->string('account', 30);
            $table->string('ifsc', 30);
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
        Schema::dropIfExists('bdm');
    }
}
