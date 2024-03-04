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
        Schema::create('lead_users_banned', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name', 30)->index()->nullable();
            $table->string('last_name', 30)->index()->nullable();
            $table->string('email', 50)->index()->nullable();
            $table->string('phone', 20)->index()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('lead_users_banned');
    }
};
