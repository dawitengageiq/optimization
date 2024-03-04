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
        Schema::create('affiliate_websites', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('affiliate_id')->unsigned()->index();
            $table->string('website_name', 255);
            $table->string('website_description', 255)->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('affiliate_websites');
    }
};
