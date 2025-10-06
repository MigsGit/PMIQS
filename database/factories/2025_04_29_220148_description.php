<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcrApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pm_descriptions', function (Blueprint $table) {
            $table->bigIncrements('pm_descriptions_id')->primary();
            $table->foreignId('pm_items_id')->references('pm_items_id')->on('pm_items')->comment ='id from dropdown_masters';
            $table->string('part_code');
            $table->longText('description_part_name')->nullable();
            $table->interger('mat_specs_length');
            $table->interger('mat_specs_width');
            $table->interger('mat_specs_height');
            $table->string('mat_raw_type');
            $table->interger('mat_raw_thickness');
            $table->interger('mat_raw_width');
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
        Schema::dropIfExists('ecr_approvals');
    }
}
