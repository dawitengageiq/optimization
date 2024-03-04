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
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('affiliate_id')->unsigned()->index()->nullable();
            $table->integer('advertiser_id')->unsigned()->index()->nullable();
            $table->string('title', 15);
            $table->string('first_name', 15);
            $table->string('middle_name', 15);
            $table->string('last_name', 15);
            $table->char('gender', 1);
            $table->string('position', 15);
            $table->string('password', 70);
            $table->string('email', 50)->unique();
            $table->string('address', 255)->nullable();
            $table->string('mobile_number', 20)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('instant_messaging', 255)->nullable();
            $table->tinyInteger('account_type');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('affiliate_id')->references('id')->on('affiliates')->onDelete('set null');
            $table->foreign('advertiser_id')->references('id')->on('advertisers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('users');
    }
};
