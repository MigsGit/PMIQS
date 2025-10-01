<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePmiApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pmi_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecrs_id')->references('id')->on('ecrs')->comment ='Ecr Id';
            //manually inject relationship in MYSQL relation view
            $table->unsignedBigInteger('rapidx_user_id')->unique()->comment('Rapidx User Id');
            $table->string('status')->default('-')->comment('PEN-Pending | APP-Approved | DIS-Disapproved');
            $table->string('approval_status')->default('RB');
            $table->bigInteger('counter');
            $table->longText('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        //ALTER TABLE `ecr_approvals` ADD  CONSTRAINT `ecr_approvals_rapidx_users_id_foreign` FOREIGN KEY (`rapidx_user_id`) REFERENCES `db_rapidx`.`users`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pmi_approvals');
    }
}
