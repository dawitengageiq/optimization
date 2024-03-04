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
        Schema::table('leads_archive', function (Blueprint $table) {
            $table->integer('path_id')->index()->unsigned()->nullable()->after('creative_id');
            $table->foreign('path_id')->references('id')->on('paths')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads_archive', function (Blueprint $table) {
            $table->dropForeign('leads_archive_path_id_foreign');
            $table->dropColumn('path_id');
        });
    }
};
