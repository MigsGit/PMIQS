<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcrQaApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecr_qa_approvals', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            /*
            firstAssign //s
            longInterval //s
            change //s
            processName //l
            workingTime //time
            trainer //big

            qcInspectorOperator //big
            trainerSampleSize //int
            trainerResult //s

            lqcSupervisor //big
            lqcSampleSize //int
            lqcResult //s

            processChangeFactor



            */
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ecr_qa_approvals');
    }
}
