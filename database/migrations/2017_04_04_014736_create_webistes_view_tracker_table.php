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
        Schema::create('webistes_view_tracker', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('website_id')->unsigned()->index();
            $table->string('email', 50);
            $table->tinyInteger('status')->unsigned();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('webistes_view_tracker');
    }
};
