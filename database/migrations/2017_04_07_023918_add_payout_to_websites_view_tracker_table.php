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
        Schema::table('websites_view_tracker', function (Blueprint $table) {
            $table->float('payout')->after('email')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites_view_tracker', function (Blueprint $table) {
            $table->dropColumn('payout');
        });
    }
};
