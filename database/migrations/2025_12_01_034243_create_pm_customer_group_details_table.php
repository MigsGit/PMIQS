<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDropdownCustomerGroupDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pm_customer_group_details', function (Blueprint $table) {
            $table->bigIncrements('pm_customer_group_details_id');
            $table->foreignId('pm_items_id')->references('pm_items_id')->on('pm_items')->comment ='Pm Items Id';
            $table->foreignId('dd_customer_groups_id')->references('dropdown_customer_groups_id')->on('dropdown_customer_groups')->comment ='Dropdown Customer Group Id';
            $table->longText('attention_name');
            $table->longText('cc_name')->nullable();
            $table->longText('subject');
            $table->longText('additional_message');
            $table->longText('terms_condition' );
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
        Schema::dropIfExists('dropdown_customer_group_details');
    }
}
