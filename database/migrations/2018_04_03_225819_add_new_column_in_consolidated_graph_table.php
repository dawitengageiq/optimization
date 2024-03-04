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
            $table->decimal('cost_per_all_clicks', 10, 2)->unsigned()->default(0)->after('mp_per_views');
            $table->decimal('cost', 10, 3)->unsigned()->default(0)->after('cost_per_all_clicks');
            $table->decimal('all_inbox_per_survey_takers', 10, 2)->unsigned()->default(0)->after('cost');
            $table->decimal('all_coreg_revenue', 10, 3)->unsigned()->default(0)->after('all_inbox_per_survey_takers');
            $table->decimal('all_coreg_revenue_per_all_coreg_views', 10, 2)->unsigned()->default(0)->after('all_coreg_revenue');
            $table->decimal('coreg_p3_revenue_vs_views', 10, 2)->unsigned()->default(0)->after('all_coreg_revenue_per_all_coreg_views');
            $table->decimal('coreg_p4_revenue_vs_views', 10, 2)->unsigned()->default(0)->after('coreg_p3_revenue_vs_views');
            $table->decimal('all_mp_revenue', 10, 3)->unsigned()->default(0)->after('coreg_p4_revenue_vs_views');
            $table->decimal('push_revenue', 10, 3)->unsigned()->default(0)->after('all_mp_revenue');
            $table->decimal('push_cpa_revenue_per_survey_takers', 10, 2)->unsigned()->default(0)->after('push_revenue');
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
                    'cost_per_all_clicks',
                    'cost',
                    'all_inbox_per_survey_takers',
                    'all_coreg_revenue',
                    'all_coreg_revenue_per_all_coreg_views',
                    'coreg_p3_revenue_vs_views',
                    'coreg_p4_revenue_vs_views',
                    'all_mp_revenue',
                    'push_revenue',
                    'push_cpa_revenue_per_survey_takers',
                ]
            );
        });
    }
};
