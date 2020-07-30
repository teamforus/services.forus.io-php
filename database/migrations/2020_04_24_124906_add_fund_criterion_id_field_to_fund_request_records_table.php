<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFundCriterionIdFieldToFundRequestRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fund_request_records', function (Blueprint $table) {
            $table->integer('fund_criterion_id')->unsigned()->nullable()->after('fund_request_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fund_request_records', function (Blueprint $table) {
            $table->dropColumn('fund_criterion_id');
        });
    }
}