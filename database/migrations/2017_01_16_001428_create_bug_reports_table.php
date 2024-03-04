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
        Schema::create('bug_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reporter_name');
            $table->string('reporter_email', 50);
            $table->string('bug_summary');
            $table->string('bug_description');
            $table->mediumText('evidences');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('bug_reports');
    }
};
