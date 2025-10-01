<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('man', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecrs_id')->references('id')->on('ecrs')->comment ='Ecr Id';
            $table->string('first_assign');
            $table->string('long_interval');
            $table->string('change');
            $table->longText('process_name');
            $table->time('working_time');
            $table->bigInteger('qc_inspector_operator')->comment('Rapidx User Id');
            $table->bigInteger('trainer')->nullable()->comment('Rapidx User Id');
            $table->integer('trainer_sample_size')->nullable();
            $table->string('trainer_result')->nullable();
            $table->bigInteger('lqc_supervisor')->nullable()->comment('Rapidx User Id');
            $table->integer('lqc_sample_size')->nullable();
            $table->string('lqc_result')->nullable();
            $table->longText('process_change_factor')->nullable();
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
        Schema::dropIfExists('man');
    }
}
