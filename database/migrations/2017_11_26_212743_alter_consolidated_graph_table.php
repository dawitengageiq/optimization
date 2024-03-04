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
            $table->decimal('source_revenue', 10, 3)->unsigned()->default(0)->change();
            $table->decimal('source_revenue_per_survey_takers', 10, 2)->unsigned()->default(0)->change();
            $table->decimal('source_revenue_per_all_clicks', 10, 2)->unsigned()->default(0)->change();
            $table->decimal('margin', 10, 2)->unsigned()->default(0)->change();
            $table->decimal('cpa_revenue', 10, 3)->unsigned()->default(0)->change();
            $table->decimal('cpa_revenue_per_views', 10, 2)->unsigned()->default(0)->change();
            $table->decimal('lsp_revenue', 10, 3)->unsigned()->default(0)->change();
            $table->decimal('lsp_revenue_vs_views', 10, 2)->unsigned()->default(0)->change();
            $table->decimal('pd_revenue', 10, 3)->unsigned()->default(0)->change();
            $table->decimal('pd_revenue_vs_views', 10, 2)->unsigned()->default(0)->change();
            $table->decimal('tb_revenue', 10, 3)->unsigned()->default(0)->change();
            $table->decimal('tb_revenue_vs_views', 10, 2)->unsigned()->default(0)->change();
            $table->decimal('iff_revenue', 10, 3)->unsigned()->default(0)->change();
            $table->decimal('iff_revenue_vs_views', 10, 2)->unsigned()->default(0)->change();
            $table->decimal('rexadz_revenue', 10, 3)->unsigned()->default(0)->change();
            $table->decimal('rexadz_revenue_vs_views', 10, 2)->unsigned()->default(0)->change();
            $table->decimal('all_inbox_revenue', 10, 3)->unsigned()->default(0)->change();
            $table->decimal('coreg_p1_revenue', 10, 3)->unsigned()->default(0)->change();
            $table->decimal('coreg_p1_revenue_vs_views', 10, 2)->unsigned()->default(0)->change();
            $table->decimal('coreg_p2_revenue', 10, 3)->unsigned()->default(0)->change();
            $table->decimal('coreg_p2_revenue_vs_views', 10, 2)->unsigned()->default(0)->change();
            $table->decimal('survey_takers_per_clicks', 10, 2)->unsigned()->default(0)->change();
            $table->decimal('cpa_per_survey_takers', 10, 2)->unsigned()->default(0)->change();
            $table->decimal('mp_per_views', 10, 2)->unsigned()->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consolidated_graph', function (Blueprint $table) {
            $table->dropColumn(
                [
                    'source_revenue',
                    'source_revenue_per_survey_takers',
                    'source_revenue_per_all_clicks',
                    'margin',
                    'cpa_revenue',
                    'cpa_revenue_per_views',
                    'lsp_revenue',
                    'lsp_revenue_vs_views',
                    'pd_revenue',
                    'pd_revenue_vs_views',
                    'tb_revenue',
                    'tb_revenue_vs_views',
                    'iff_revenue',
                    'iff_revenue_vs_views',
                    'rexadz_revenue',
                    'rexadz_revenue_vs_views',
                    'all_inbox_revenue',
                    'coreg_p1_revenue',
                    'coreg_p1_revenue_vs_views',
                    'coreg_p2_revenue',
                    'coreg_p2_revenue_vs_views',
                    'survey_takers_per_clicks',
                    'cpa_per_survey_takers',
                    'mp_per_views',
                ]
            );
        });
    }
};
