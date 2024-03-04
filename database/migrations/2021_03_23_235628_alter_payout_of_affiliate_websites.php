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
        DB::statement('alter table affiliate_websites modify payout DOUBLE(10,3) DEFAULT 0');
        DB::connection('secondary')->statement('alter table affiliate_websites modify payout DOUBLE(10,3) DEFAULT 0');
        // Schema::table('affiliate_websites', function (Blueprint $table) {
        //     $table->double('payout',10,3)->change();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
