<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\Billdata as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Kyslik\ColumnSortable\Sortable;

class Billdata extends Model
{
    use HasFactory, Notifiable;
    use Sortable;

    // 参照させたいSQLのテーブル名を指定
    protected $table = 'billdatas';

    //追記(ソートに使うカラムを指定
    public $sortable = ['id','filepath','filename','filesize','extension_flg','urgent_flg','created_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'filepath',
        'filename',
        'filesize',
        'organization_id',
        'extension_flg',
        'urgent_flg',
        'customer_id',
        'created_at',
    ];

    protected $dates = [
        'created_at',
    ];

}
