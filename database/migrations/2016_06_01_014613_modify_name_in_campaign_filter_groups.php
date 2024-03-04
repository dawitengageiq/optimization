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
        //SHOW INDEX FROM campaign_filter_groups
        Schema::table('campaign_filter_groups', function ($table) {
            $table->dropUnique('campaign_filter_groups_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_filter_groups', function (Blueprint $table) {
            $table->unique('name');
        });
    }
};
