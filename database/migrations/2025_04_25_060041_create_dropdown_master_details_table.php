<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDropdownMasterDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dropdown_master_details', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('status')->nullable();
            $table->foreignId('dropdown_masters_id')->references('id')->on('dropdown_masters')->comment ='id from dropdown_masters';
            $table->string('dropdown_masters_details')->nullable();
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->comment('Rapidx User Id');
            $table->unsignedBigInteger('updated_by')->comment('Rapidx User Id');
            $table->softDeletes();
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
        Schema::dropIfExists('dropdown_master_details');
    }
}
