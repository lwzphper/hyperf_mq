<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateMqErrorLogTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mq_error_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('mq_info')->comment('队列相关信息');
            $table->text('error_msg')->comment('错误信息');
            $table->timestamps();

            $table->comment('消费错误日志');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mq_error_log');
    }
}
