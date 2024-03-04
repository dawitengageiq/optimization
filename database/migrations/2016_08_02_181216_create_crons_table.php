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
        Schema::create('crons', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('leads_queued')->default(0);
            $table->integer('leads_processed')->index()->default(0);
            $table->integer('leads_waiting')->index()->default(0);
            $table->dateTime('time_started')->index()->nullable();
            $table->dateTime('time_ended')->index()->nullable();
            $table->smallInteger('status')->index()->default(1);
            $table->text('lead_ids')->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('crons');
    }
};
