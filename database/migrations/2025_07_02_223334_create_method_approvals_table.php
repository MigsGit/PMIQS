<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMethodApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('method_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('methods_id')->references('id')->on('methods')->comment ='Method Id';
            $table->foreignId('ecrs_id')->references('id')->on('ecrs')->comment ='Ecr Id';
            //manually inject relationship in MYSQL relation view
            $table->unsignedBigInteger('rapidx_user_id')->comment('Rapidx User Id');
            $table->string('status')->default('-')->comment('PEN-Pending | APP-Approved | DIS-Disapproved');
            $table->string('approval_status');
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
        Schema::dropIfExists('method_approvals');
    }
}
