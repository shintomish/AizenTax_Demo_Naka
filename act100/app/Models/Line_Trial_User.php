<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Line_Trial_User extends Model
{
    use HasFactory;

    // 参照させたいSQLのテーブル名を指定
    protected $table = 'line_trial_users';

    protected $fillable = [
        'line_user_id',
        'users_name',
    ];
}
