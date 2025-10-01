<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            //Not Working Both Foreign Key Function
            // $table->foreignId('rapidx_user_id')->constrained('rapidx.users', 'id')->comment('Rapidx User Id');
            // $table->foreignId('rapidx_users_id')->references('id')->on('rapidx.users')->comment ='Rapidx User Id';
            $table->string('emp_no');
            $table->string('role')->nullable();
            $table->string('section')->nullable();
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
        Schema::dropIfExists('user_roles');
    }
}
