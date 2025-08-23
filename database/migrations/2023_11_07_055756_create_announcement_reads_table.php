<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncementReadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('announcement_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('ユーザーID');
            $table->unsignedBigInteger('announcement_id')->comment('お知らせID');
            $table->boolean('read')->default(false)->comment('未読 or 既読');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('announcement_id')->references('id')->on('announcements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('announcement_reads');
    }
}
