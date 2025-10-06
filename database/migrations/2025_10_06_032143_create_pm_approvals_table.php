<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePmApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pm_approvals', function (Blueprint $table) {
            $table->bigIncrements('pm_approvals_id');
            $table->foreignId('pm_items_id')->references('pm_items_id')->on('pm_items')->comment ='Ecr Id';
            $table->unsignedBigInteger('rapidx_user_id')->comment('Rapidx User Id');
            $table->string('status')->default('-')->comment('PEN-Pending | APP-Approved | DIS-Disapproved');
            $table->string('approval_status')->default('PREPBY')->comment('PREPBY-Preparedby |CHCKBY-Checkedby | NOTEDBY-Notedby |  APPBY - Approvedby');
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
        Schema::dropIfExists('pm_approvals');
    }
}
