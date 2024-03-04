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
        if (config('app.type') == 'reports') {
            // Schema::table('affiliate_reports', function (Blueprint $table) {
            //     $table->dropForeign("affiliate_reports_affiliate_id_foreign");
            //     $table->dropForeign("affiliate_reports_revenue_tracker_id_foreign");
            // });
            Schema::table('affiliate_reports', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $foreigns = $sm->listTableForeignKeys('affiliate_reports');
                foreach ($foreigns as $fk) {
                    if ($fk->getName() == 'affiliate_reports_campaign_id_foreign') {
                        $table->dropForeign('affiliate_reports_campaign_id_foreign');
                    }
                }
            });

            Schema::table('cake_revenues', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $foreigns = $sm->listTableForeignKeys('cake_revenues');
                foreach ($foreigns as $fk) {
                    if ($fk->getName() == 'cake_revenues_affiliate_id_foreign') {
                        $table->dropForeign('cake_revenues_affiliate_id_foreign');
                    }
                    if ($fk->getName() == 'cake_revenues_revenue_tracker_id_foreign') {
                        $table->dropForeign('cake_revenues_revenue_tracker_id_foreign');
                    }
                }
            });

            Schema::table('campaign_revenue_view_statistics', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $foreigns = $sm->listTableForeignKeys('campaign_revenue_view_statistics');
                foreach ($foreigns as $fk) {
                    if ($fk->getName() == 'campaign_revenue_view_statistics_campaign_id_foreign') {
                        $table->dropForeign('campaign_revenue_view_statistics_campaign_id_foreign');
                    }
                }
            });

            Schema::table('clicks_vs_registration_statistics', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $foreigns = $sm->listTableForeignKeys('clicks_vs_registration_statistics');
                foreach ($foreigns as $fk) {
                    if ($fk->getName() == 'clicks_vs_registration_statistics_affiliate_id_foreign') {
                        $table->dropForeign('clicks_vs_registration_statistics_affiliate_id_foreign');
                    }
                    if ($fk->getName() == 'clicks_vs_registration_statistics_revenue_tracker_id_foreign') {
                        $table->dropForeign('clicks_vs_registration_statistics_revenue_tracker_id_foreign');
                    }
                }
            });

            Schema::table('creative_reports', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $foreigns = $sm->listTableForeignKeys('creative_reports');
                foreach ($foreigns as $fk) {
                    if ($fk->getName() == 'creative_reports_affiliate_id_foreign') {
                        $table->dropForeign('creative_reports_affiliate_id_foreign');
                    }
                    if ($fk->getName() == 'creative_reports_campaign_id_foreign') {
                        $table->dropForeign('creative_reports_campaign_id_foreign');
                    }
                    if ($fk->getName() == 'creative_reports_creative_id_foreign') {
                        $table->dropForeign('creative_reports_creative_id_foreign');
                    }
                    if ($fk->getName() == 'creative_reports_path_id_foreign') {
                        $table->dropForeign('creative_reports_path_id_foreign');
                    }
                }
            });

            Schema::table('handp_affiliate_reports', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $foreigns = $sm->listTableForeignKeys('handp_affiliate_reports');
                foreach ($foreigns as $fk) {
                    if ($fk->getName() == 'handp_affiliate_reports_affiliate_id_foreign') {
                        $table->dropForeign('handp_affiliate_reports_affiliate_id_foreign');
                    }
                    if ($fk->getName() == 'handp_affiliate_reports_campaign_id_foreign') {
                        $table->dropForeign('handp_affiliate_reports_campaign_id_foreign');
                    }
                }
            });

            Schema::table('iframe_affiliate_reports', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $foreigns = $sm->listTableForeignKeys('iframe_affiliate_reports');
                foreach ($foreigns as $fk) {
                    if ($fk->getName() == 'iframe_affiliate_reports_affiliate_id_foreign') {
                        $table->dropForeign('iframe_affiliate_reports_affiliate_id_foreign');
                    }
                    if ($fk->getName() == 'iframe_affiliate_reports_campaign_id_foreign') {
                        $table->dropForeign('iframe_affiliate_reports_campaign_id_foreign');
                    }
                    if ($fk->getName() == 'iframe_affiliate_reports_revenue_tracker_id_foreign') {
                        $table->dropForeign('iframe_affiliate_reports_revenue_tracker_id_foreign');
                    }
                }
            });

            Schema::table('prepop_statistics', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $foreigns = $sm->listTableForeignKeys('prepop_statistics');
                foreach ($foreigns as $fk) {
                    if ($fk->getName() == 'prepop_statistics_affiliate_id_foreign') {
                        $table->dropForeign('prepop_statistics_affiliate_id_foreign');
                    }
                    if ($fk->getName() == 'prepop_statistics_revenue_tracker_id_foreign') {
                        $table->dropForeign('prepop_statistics_revenue_tracker_id_foreign');
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
