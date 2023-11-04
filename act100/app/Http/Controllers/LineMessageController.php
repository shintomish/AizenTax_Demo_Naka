<?php

// 事務所 体験者データ確認
namespace App\Http\Controllers;

use App\Models\Line_Trial_User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LineMessageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function input()
    {
        Log::info('linemessage input START');

        $line_trial_users = DB::table('line_trial_user')
                    ->orderBy('created_at', 'desc')
                    ->sortable()
                    ->paginate(100);

        $common_no = 'linemessage';

        Log::info('linemessage input END');

        $compacts = compact( 'common_no', 'line_trial_users' );

        return view( 'linemessage.input', $compacts );
    }

    /**
     * [webapi] line_trial_userテーブルの更新
     */
    public function update_api(Request $request)
    {
        Log::info('update_api linemessage START');

        // Log::debug('update_api request = ' .print_r($request->all(),true));
        $id = $request->input('id');

        $urgent_flg     = 1;  // 1:未作成 2:作成済

        $counts = array();
        $update = [];
        $update['urgent_flg'] = $urgent_flg;
        $update['updated_at'] = date('Y-m-d H:i:s');
        // Log::debug('update_api linemessage update : ' . print_r($update,true));

        $status = array();
        DB::beginTransaction();
        Log::info('update_api linemessage beginTransaction - start');
        try{
            // 更新処理
            Line_Trial_User::where( 'id', $id )->update($update);

            $status = array( 'error_code' => 0, 'message'  => 'Your data has been changed!' );
            $counts = 1;
            DB::commit();
            Log::info('update_api linemessage beginTransaction - end');
        }
        catch(Exception $e){
            Log::error('update_api linemessage exception : ' . $e->getMessage());
            DB::rollback();
            Log::info('update_api linemessage beginTransaction - end(rollback)');
            echo "エラー：" . $e->getMessage();
            $counts = 0;
            $status = array( 'error_code' => 501, 'message'  => $e->getMessage() );
        }

        Log::info('update_api linemessage END');
        return response()->json([ compact('status','counts') ]);

    }

}
