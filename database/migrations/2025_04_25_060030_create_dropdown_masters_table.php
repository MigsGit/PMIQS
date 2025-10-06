<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDropdownMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dropdown_masters', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('status')->nullable();
            $table->string('dropdown_masters')->nullable();
            $table->string('category')->nullable();
            $table->string('table_reference')->nullable();
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->comment('Rapidx User Id');
            $table->unsignedBigInteger('updated_by')->comment('Rapidx User Id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dropdown_masters');
    }
}
