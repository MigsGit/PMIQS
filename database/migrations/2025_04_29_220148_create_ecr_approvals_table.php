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
        Schema::create('ecr_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecrs_id')->references('id')->on('ecrs')->comment ='Ecr Id';
            //manually inject relationship in MYSQL relation view
            $table->unsignedBigInteger('rapidx_user_id')->comment('Rapidx User Id');
            //MYSQL restrict this function if different database
            // $table->foreignId('rapidx_users_id')->references('id')->on('rapidx.users')->comment ='Rapidx User Id';
            $table->string('status')->default('-')->comment('PEN-Pending | APP-Approved | DIS-Disapproved');
            $table->string('approval_status')->default('RB');
            $table->bigInteger('counter');
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
        Schema::dropIfExists('ecr_approvals');
    }
}
