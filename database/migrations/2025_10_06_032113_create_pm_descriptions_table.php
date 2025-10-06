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
            $table->string('part_code');
            $table->longText('description_part_name')->nullable();
            $table->integer('mat_specs_length');
            $table->integer('mat_specs_width');
            $table->integer('mat_specs_height');
            $table->string('mat_raw_type');
            $table->integer('mat_raw_thickness');
            $table->integer('mat_raw_width');
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
