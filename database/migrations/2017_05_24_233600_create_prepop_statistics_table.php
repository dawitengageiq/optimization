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
        Schema::create('prepop_statistics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('affiliate_id')->unsigned()->index();
            $table->integer('revenue_tracker_id')->unsigned()->index();
            $table->integer('total_clicks')->unsigned()->index();
            $table->integer('prepop_count')->unsigned()->index();
            $table->integer('no_prepop_count')->unsigned()->index();
            $table->integer('prepop_with_errors_count')->unsigned()->index();
            $table->decimal('no_prepop_percentage', 8, 3);
            $table->decimal('prepop_with_errors_percentage', 8, 3);
            $table->date('created_at');
            //$table->timestamps();

            $table->foreign('affiliate_id')->references('affiliate_id')->on('revenue_tracker_cake_statistics')->onDelete('cascade');
            $table->foreign('revenue_tracker_id')->references('revenue_tracker_id')->on('affiliate_revenue_trackers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('prepop_statistics');
    }
};
