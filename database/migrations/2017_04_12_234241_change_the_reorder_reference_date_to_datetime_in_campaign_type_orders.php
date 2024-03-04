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
        Schema::table('campaign_type_orders', function (Blueprint $table) {
            $table->dateTime('reorder_reference_date')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_type_orders', function (Blueprint $table) {
            $table->date('reorder_reference_date')->change();
        });
    }
};
