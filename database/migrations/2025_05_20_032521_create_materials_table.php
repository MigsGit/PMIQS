<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecrs_id')->references('id')->on('ecrs')->comment ='Ecr Id';
            $table->string('icp');
            $table->string('status');
            $table->string('gp');
            $table->string('pd_material');
            $table->string('msds');
            $table->string('qoutation');
            $table->string('coc');
            $table->string('material_sample');
            $table->foreignId('material_supplier')->references('id')->on('dropdown_master_details')->comment ='DropdownMasterDetails Id';
            $table->foreignId('material_color')->references('id')->on('dropdown_master_details')->comment ='DropdownMasterDetails Id';
            $table->string('rohs')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecrs_id')->references('id')->on('ecrs')->comment ='Ecr Id';
            $table->string('status');
            $table->string('approval_status');
            $table->string('icp');
            $table->string('gp');
            $table->string('pd_material');
            $table->string('msds');
            $table->string('qoutation');
            $table->string('coc');
            $table->string('material_sample');
            $table->foreignId('material_supplier')->references('id')->on('dropdown_master_details')->comment ='DropdownMasterDetails Id';
            $table->foreignId('material_color')->references('id')->on('dropdown_master_details')->comment ='DropdownMasterDetails Id';
            $table->string('rohs')->nullable();
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
        Schema::dropIfExists('materials');
    }
}
