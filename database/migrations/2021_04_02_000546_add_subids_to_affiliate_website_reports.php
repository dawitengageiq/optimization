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
        Schema::table('affiliate_website_reports', function (Blueprint $table) {
            $table->integer('affiliate_id')->unsigned()->nullable();
            $table->integer('revenue_tracker_id')->unsigned()->nullable();
            $table->string('s1')->nullable();
            $table->string('s2')->nullable();
            $table->string('s3')->nullable();
            $table->string('s4')->nullable();
            $table->string('s5')->nullable();
        });

        Schema::table('websites_view_tracker', function (Blueprint $table) {
            $table->integer('affiliate_id')->unsigned()->nullable();
            $table->integer('revenue_tracker_id')->unsigned()->nullable();
            $table->string('s1')->nullable();
            $table->string('s2')->nullable();
            $table->string('s3')->nullable();
            $table->string('s4')->nullable();
            $table->string('s5')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_website_reports', function (Blueprint $table) {
            $table->dropColumn([
                'affiliate_id',
                'revenue_tracker_id',
                's1',
                's2',
                's3',
                's4',
                's5',
            ]);
        });

        Schema::table('websites_view_tracker', function (Blueprint $table) {
            $table->dropColumn([
                'affiliate_id',
                'revenue_tracker_id',
                's1',
                's2',
                's3',
                's4',
                's5',
            ]);
        });
    }
};
