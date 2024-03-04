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
        Schema::create('click_log_trace_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('click_date')->index();
            $table->string('click_id', 30);
            $table->string('email', 50)->index();
            $table->integer('affiliate_id')->unsigned()->index();
            $table->integer('revenue_tracker_id')->unsigned()->index();
            $table->string('ip', 15);
            $table->tinyInteger('is_dbprepoped')->unsigned();
            $table->integer('reg_count')->unsigned();
            $table->integer('first_entry_rev_id')->unsigned();
            $table->timestamp('first_entry_timestamp');
            $table->integer('last_entry_rev_id')->unsigned();
            $table->timestamp('last_entry_timestamp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('click_log_trace_infos');
    }
};
