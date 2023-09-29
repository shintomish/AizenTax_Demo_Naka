<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Message extends Model
{
    use HasFactory;
    use SerializeDate;

    // 参照させたいSQLのテーブル名を指定
    protected $table = 'messages';

    // messagesテーブルのcreated_atの値をY-m-d H:i:sの形で参照したい場合
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
