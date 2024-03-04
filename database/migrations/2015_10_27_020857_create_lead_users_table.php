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
        Schema::create('lead_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name', 30);
            $table->string('last_name', 30);
            $table->string('email', 50)->unique();
            $table->date('birthdate');
            $table->char('gender', 1)->nullable();
            $table->string('zip', 8)->nullable();
            $table->string('city', 25)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('address1', 255)->nullable();
            $table->string('address2', 255)->nullable();
            $table->char('ethnicity', 1)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('source_url', 1000)->nullable();
            $table->string('ip', 15);
            $table->tinyInteger('is_mobile')->unsigned()->nullable();
            $table->tinyInteger('status')->unsigned()->nullable();
            $table->string('response', 1000)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('lead_users');
    }
};
