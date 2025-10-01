<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManChecklistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('man_id')->references('id')->on('man')->comment ='Ecr Id';
            $table->foreignId('dropdown_master_details_id')->references('id')->on('dropdown_master_details')->comment ='Dropdown MasterDetails Id';
            $table->string('decision');
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
        Schema::dropIfExists('man_checklists');
    }
}
