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
        Schema::table('affiliate_revenue_trackers', function (Blueprint $table) {
            $table->boolean('sib_s1')->default(0);
            $table->boolean('sib_s2')->default(0);
            $table->boolean('sib_s3')->default(0);
            $table->boolean('sib_s4')->default(0);

            $table->boolean('nsib_s1')->nullable();
            $table->boolean('nsib_s2')->nullable();
            $table->boolean('nsib_s3')->nullable();
            $table->boolean('nsib_s4')->nullable();

            $table->boolean('rsib_s1')->default(0);
            $table->boolean('rsib_s2')->default(0);
            $table->boolean('rsib_s3')->default(0);
            $table->boolean('rsib_s4')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_revenue_trackers', function (Blueprint $table) {
            $table->dropColumn(['sib_s1', 'sib_s2', 'sib_s3', 'sib_s4', 'nsib_s1', 'nsib_s2', 'nsib_s3', 'nsib_s4', 'rsib_s1', 'rsib_s2', 'rsib_s3', 'rsib_s4']);
        });
    }
};
