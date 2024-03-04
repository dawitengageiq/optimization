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
        Schema::create('consolidated_graph', function (Blueprint $table) {

            $table->increments('id')->unsigned()->index();
            $table->integer('revenue_tracker_id')->unsigned()->index();
            // $table->integer('affiliate_id')->unsigned()->index();
            $table->integer('survey_takers')->unsigned()->default(0);
            $table->decimal('source_revenue', 8, 3)->unsigned()->default(0);
            $table->decimal('source_revenue_per_survey_takers', 8, 2)->unsigned()->default(0);
            $table->integer('all_clicks')->unsigned()->default(0);
            $table->decimal('source_revenue_per_all_clicks', 8, 2)->unsigned()->default(0);
            $table->decimal('margin', 8, 2)->unsigned()->default(0);
            $table->decimal('cpa_revenue', 8, 3)->unsigned()->default(0);
            $table->decimal('cpa_revenue_per_views', 8, 2)->unsigned()->default(0);
            $table->decimal('lsp_revenue', 8, 3)->unsigned()->default(0);
            $table->integer('lsp_views')->unsigned()->default(0);
            $table->decimal('lsp_revenue_vs_views', 8, 2)->unsigned()->default(0);
            $table->decimal('pd_revenue', 8, 3)->unsigned()->default(0);
            $table->decimal('pd_revenue_vs_views', 8, 2)->unsigned()->default(0);
            $table->decimal('tb_revenue', 8, 3)->unsigned()->default(0);
            $table->decimal('tb_revenue_vs_views', 8, 2)->unsigned()->default(0);
            $table->decimal('iff_revenue', 8, 3)->unsigned()->default(0);
            $table->decimal('iff_revenue_vs_views', 8, 2)->unsigned()->default(0);
            $table->decimal('rexadz_revenue', 8, 3)->unsigned()->default(0);
            $table->decimal('rexadz_revenue_vs_views', 8, 2)->unsigned()->default(0);
            $table->decimal('all_inbox_revenue', 8, 3)->unsigned()->default(0);
            $table->decimal('coreg_p1_revenue', 8, 3)->unsigned()->default(0);
            $table->decimal('coreg_p1_revenue_vs_views', 8, 2)->unsigned()->default(0);
            $table->decimal('coreg_p2_revenue', 8, 3)->unsigned()->default(0);
            $table->decimal('coreg_p2_revenue_vs_views', 8, 2)->unsigned()->default(0);
            $table->decimal('survey_takers_per_clicks', 8, 2)->unsigned()->default(0);
            $table->decimal('cpa_per_survey_takers', 8, 2)->unsigned()->default(0);
            $table->decimal('mp_per_views', 8, 2)->unsigned()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        chema::drop('consolidated_graph');
    }
};
