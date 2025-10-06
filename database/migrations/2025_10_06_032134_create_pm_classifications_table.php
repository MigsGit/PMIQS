<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePmClassificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pm_classifications', function (Blueprint $table) {
            $table->bigIncrements('pm_classifications_id');
            $table->foreignId('pm_descriptions_id')->references('pm_descriptions_id')->on('pm_descriptions')->comment ='id from dropdown_masters';
            $table->longText('classification')->nullable();
            $table->integer('qty');
            $table->foreignId('uom')->references('id')->on('dropdown_master_details')->comment ='Dropdown MasterDetails Id';
            $table->integer('unit_price');
            $table->longText('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pm_classifications');
    }
}
