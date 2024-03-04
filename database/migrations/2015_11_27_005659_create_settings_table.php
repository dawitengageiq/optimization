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
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 100);
            $table->string('name', 100);
            $table->text('description');
            $table->string('string_value', 100)->nullable();
            $table->smallInteger('integer_value')->nullable();
            $table->double('double_value', 4, 3)->nullable();
            $table->date('date_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('settings');
    }
};
