<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePmItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pm_items', function (Blueprint $table) {
            $table->bigIncrements('pm_items_id');
            $table->string('status')->default('FORUP')->comment('FORUP-For Update | FORAPP-For Approval | DIS- Disapproved | CAN- Cancelled');
            $table->string('approval_status')->default('PREPBY')->comment('PREPBY-Preparedby |CHCKBY-Checkedby | NOTEDBY-Notedby |  APPBY - Approvedby');
            $table->string('item_no');
            $table->string('type');
            $table->string('category');
            $table->unsignedBigInteger('created_by')->comment('Rapidx User Id');
            $table->unsignedBigInteger('updated_by')->comment('Rapidx User Id');
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
        Schema::dropIfExists('pm_items');
    }
}
