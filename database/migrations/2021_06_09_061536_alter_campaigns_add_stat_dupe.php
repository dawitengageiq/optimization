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
        Schema::table('campaigns', function (Blueprint $table) {
            $table->tinyInteger('status_dupe')->default(0);
            $table->boolean('linkout_cake_status')->default(0);
        });

        DB::statement('UPDATE campaigns SET status_dupe=status');
        DB::statement('UPDATE campaigns SET linkout_cake_status = CASE WHEN campaign_type = 5 AND status != 0 THEN 1 ELSE 0 END');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('status_dupe');
            $table->dropColumn('linkout_cake_status');
        });
    }
};
