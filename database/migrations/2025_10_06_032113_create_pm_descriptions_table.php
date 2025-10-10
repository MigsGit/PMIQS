<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePmDescriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pm_descriptions', function (Blueprint $table) {
            $table->bigIncrements('pm_descriptions_id');
            $table->foreignId('pm_items_id')->references('pm_items_id')->on('pm_items')->comment ='id from dropdown_masters';
            $table->bigInteger('item_no');
            $table->string('part_code')->nullable();
            $table->longText('description_part_name')->nullable();
            $table->integer('mat_specs_length')->nullable();
            $table->integer('mat_specs_width')->nullable();
            $table->integer('mat_specs_height')->nullable();
            $table->string('mat_raw_type')->nullable();
            $table->integer('mat_raw_thickness')->nullable();
            $table->integer('mat_raw_width')->nullable();
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
        Schema::dropIfExists('pm_descriptions');
    }
}
