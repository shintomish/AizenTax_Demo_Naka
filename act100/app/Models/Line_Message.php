<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Line_Message extends Model
{
    use HasFactory;

    // 参照させたいSQLのテーブル名を指定
    protected $table = 'line_messages';

    protected $fillable = [
        'line_user_id',
        'line_message_id',
        'text',
    ];
}
