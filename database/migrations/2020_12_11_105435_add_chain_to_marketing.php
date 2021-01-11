<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChainToMarketing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Businesshead', function (Blueprint $table) {
            $table->integer('chain_id')->unsigned()->index();
            $table->foreign('chain_id')->references('id')->on('country_head')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('regionalmanager', function (Blueprint $table) {
            $table->integer('chain_id')->unsigned()->index();
            $table->foreign('chain_id')->references('id')->on('Businesshead')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('supplyhead', function (Blueprint $table) {
            $table->integer('chain_id')->unsigned()->index();
            $table->foreign('chain_id')->references('id')->on('regionalmanager')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('servicecebdm', function (Blueprint $table) {
            $table->integer('chain_id')->unsigned()->index();
            $table->foreign('chain_id')->references('id')->on('supplyhead')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('bdm', function (Blueprint $table) {
            $table->integer('chain_id')->unsigned()->index();
            $table->foreign('chain_id')->references('id')->on('servicecebdm')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('Seniorbde', function (Blueprint $table) {
            $table->integer('chain_id')->unsigned()->index();
            $table->foreign('chain_id')->references('id')->on('bdm')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('bde', function (Blueprint $table) {
            $table->integer('chain_id')->unsigned()->index();
            $table->foreign('chain_id')->references('id')->on('Seniorbde')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Businesshead', function (Blueprint $table) {
            $table->dropForeign('businesshead_chain_id_foreign');
            $table->dropColumn('chain_id');
        });
        Schema::table('regionalmanager', function (Blueprint $table) {
            $table->dropForeign('regionalmanager_chain_id_foreign');
            $table->dropColumn('chain_id');
        });
        Schema::table('supplyhead', function (Blueprint $table) {
            $table->dropForeign('supplyhead_chain_id_foreign');
            $table->dropColumn('chain_id');
        });
        Schema::table('servicecebdm', function (Blueprint $table) {
            $table->dropForeign('servicecebdm_chain_id_foreign');
            $table->dropColumn('chain_id');
        });
        Schema::table('bdm', function (Blueprint $table) {
            $table->dropForeign('bdm_chain_id_foreign');
            $table->dropColumn('chain_id');
        });
        Schema::table('Seniorbde', function (Blueprint $table) {
            $table->dropForeign('seniorbde_chain_id_foreign');
            $table->dropColumn('chain_id');
        });
        Schema::table('bde', function (Blueprint $table) {
            $table->dropForeign('bde_chain_id_foreign');
            $table->dropColumn('chain_id');
        });
    }
}
