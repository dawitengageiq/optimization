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
        Schema::table('affiliate_websites', function (Blueprint $table) {
            $table->float('payout')->after('website_description')->unsigned();
            //$table->float('payout')->change()->after('website_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_websites', function (Blueprint $table) {
            $table->dropColumn('payout');
        });
    }
};
