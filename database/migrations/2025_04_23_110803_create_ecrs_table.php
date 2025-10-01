<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecrs', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('IA')->comment('IA - Internal Approval | QA Approval | DO- Done');
            $table->string('ecr_no');
            $table->longText('original_filename')->nullable();
            $table->longText('filtered_document_name')->nullable();
            $table->string('approval_status')->default('RB');
            $table->string('category');
            $table->string('internal_external');
            $table->string('customer_name');
            $table->string('part_no');
            $table->string('part_name');
            $table->string('device_name');
            $table->string('product_line'); //dropdown or session
            $table->string('section'); //dropdown or session
            $table->string('customer_ec_no');
            $table->date('date_of_request');
            $table->unsignedBigInteger('created_by')->unique()->comment('Rapidx User Id');
            $table->unsignedBigInteger('updated_by')->unique()->comment('Rapidx User Id');
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
        Schema::dropIfExists('ecrs');
    }
}
