<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// return new class extends Migration
class CreateLineMessagesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('linemessages', function (Blueprint $table) {
            $table->id();
            $table->string('line_user_id')->comment('LINEユーザーID');
            $table->string('line_message_id')->nullable()->comment('LINEメッセージID');
            $table->string('text')->comment('テキスト');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('linemessages');
    }
};
