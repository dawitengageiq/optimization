<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('action_role', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role_id')->unsigned()->index()->nullable();
            $table->integer('action_id')->unsigned()->index()->nullable();
            $table->boolean('permitted');
            //$table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('action_id')->references('id')->on('actions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('action_role');
    }
};
