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
            $table->decimal('coreg_p3_revenue', 10, 3)->unsigned()->default(0)->after('all_coreg_revenue_per_all_coreg_views');
            $table->decimal('coreg_p4_revenue', 10, 3)->unsigned()->default(0)->after('coreg_p3_revenue_vs_views');

            $table->integer('cpa_views')->unsigned()->default(0)->after('cpa_revenue');
            $table->integer('pd_views')->unsigned()->default(0)->after('pd_revenue');
            $table->integer('tb_views')->unsigned()->default(0)->after('tb_revenue');
            $table->integer('iff_views')->unsigned()->default(0)->after('iff_revenue');
            $table->integer('rexadz_views')->unsigned()->default(0)->after('rexadz_revenue');

            $table->integer('coreg_p1_views')->unsigned()->default(0)->after('coreg_p1_revenue');
            $table->integer('coreg_p2_views')->unsigned()->default(0)->after('coreg_p2_revenue');
            $table->integer('coreg_p3_views')->unsigned()->default(0)->after('coreg_p3_revenue');
            $table->integer('coreg_p4_views')->unsigned()->default(0)->after('coreg_p4_revenue');

            $table->integer('all_coreg_views')->unsigned()->default(0)->after('all_coreg_revenue');
            $table->integer('all_mp_views')->unsigned()->default(0)->after('all_mp_revenue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consolidated_graph', function (Blueprint $table) {
            $table->dropColumn(['coreg_p3_revenue', 'coreg_p4_revenue', 'cpa_views', 'pd_views', 'tb_views', 'iff_views', 'rexadz_views', 'coreg_p1_views', 'coreg_p2_views', 'coreg_p3_views', 'coreg_p4_views', 'all_coreg_views', 'all_mp_views']);
        });
    }
};
