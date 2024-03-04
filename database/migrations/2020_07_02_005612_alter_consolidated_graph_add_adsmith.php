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
        Schema::table('consolidated_graph', function (Blueprint $table) {

            $table->decimal('adsmith_revenue_vs_views', 8, 2)->unsigned()->default(0)->after('mp_per_views');
            $table->integer('adsmith_views')->unsigned()->default(0)->after('mp_per_views');
            $table->decimal('adsmith_revenue', 8, 3)->unsigned()->default(0)->after('mp_per_views');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consolidated_graph', function (Blueprint $table) {
            $table->dropColumn(['adsmith_revenue', 'adsmith_revenue_vs_views', 'adsmith_views']);
        });
    }
};
