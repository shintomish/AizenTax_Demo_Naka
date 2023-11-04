<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Kyslik\ColumnSortable\Sortable;

class Line_Trial_User_History extends Model
{
    use HasFactory;
    use Sortable;

    public $sortable = ['id','urgent_flg','filepath','filename','filesize','created_at'];   //追記(ソートに使うカラムを指定

    // 参照させたいSQLのテーブル名を指定
    protected $table = 'line_trial_users';

    protected $fillable = [
        'line_user_id',
        'users_name',
        'urgent_flg'
    ];

    protected $dates = [
        'created_at',
    ];
}
