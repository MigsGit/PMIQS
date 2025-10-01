<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcrRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecr_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecrs_id')->references('id')->on('ecrs')->comment ='Ecr Id';
            $table->foreignId('classification_requirements_id')->references('id')->on('classification_requirements')->comment ='Classifications Requirement Id';
            $table->string('decision');
            $table->longText('original_filename')->nullable();
            $table->longText('filtered_document_name')->nullable();
            $table->bigtInt('updated_by')->nullable();
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
        Schema::dropIfExists('ecr_requirements');
    }
}
