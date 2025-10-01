<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcrDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecr_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecrs_id')->references('id')->on('ecrs')->comment ='Ecr Id';
            $table->foreignId('description_of_change')->references('id')->on('dropdown_master_details')->comment ='DropdownMasterDetails Id';
            $table->foreignId('reason_of_change')->references('id')->on('dropdown_master_details')->comment ='EDropdownMasterDetails Id';
            $table->longText('type_of_part')->nullable()->comment ='For Material Category Only';
            $table->date('change_imp_date')->nullable();
            $table->date('doc_sub_date')->nullable();
            $table->longText('doc_to_be_sub')->nullable();
            $table->longText('customer_approval')->nullable()->comment ='For Environment Category Only';
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
        Schema::dropIfExists('ecr_details');
    }
}
