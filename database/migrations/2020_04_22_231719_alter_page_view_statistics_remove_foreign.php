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
        Schema::table('page_view_statistics', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $foreigns = $sm->listTableForeignKeys('page_view_statistics');
            foreach ($foreigns as $fk) {
                if ($fk->getName() == 'page_view_statistics_affiliate_id_foreign') {
                    $table->dropForeign('page_view_statistics_affiliate_id_foreign');
                }
                if ($fk->getName() == 'page_view_statistics_revenue_tracker_id_foreign') {
                    $table->dropForeign('page_view_statistics_revenue_tracker_id_foreign');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
