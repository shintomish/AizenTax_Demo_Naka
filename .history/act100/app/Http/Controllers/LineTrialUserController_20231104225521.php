<?php

// 事務所 体験者データ確認
namespace App\Http\Controllers;

use App\Models\Line_Trial_Users;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LineTrialUserController extends Controller
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
        Log::info('linetrialuser input START');
        $linetrialusers = Line_Trial_Users::whereNull('deleted_at')
                        ->sortable()
                        ->orderByRaw('created_at DESC')
                        ->paginate(100);

        $common_no = 'linetrialuser';

        Log::info('linetrialuser input END');

        $compacts = compact( 'common_no', 'linetrialusers' );

        return view( 'linetrialuser.input', $compacts );
    }

    /**
     * [webapi] linetrialuserテーブルの更新
     */
    public function update_api(Request $request)
    {
        Log::info('update_api linetrialuser START');

        // Log::debug('update_api request = ' .print_r($request->all(),true));
        $id = $request->input('id');
        $users_name       = $request->input('users_name');
        $reservationed_at = $request->input('reservationed_at');

        $counts = array();
        $update = [];
        if( $request->exists('users_name')       ) $update['users_name']       = $request->input('users_name');
        if( $request->exists('reservationed_at') ) $update['reservationed_at'] = $request->input('reservationed_at');
        $update['updated_at']       = date('Y-m-d H:i:s');
        // Log::debug('update_api linetrialuser update : ' . print_r($update,true));

        $status = array();
        DB::beginTransaction();
        Log::info('update_api linetrialuser beginTransaction - start');
        try{
            // 更新処理
            Line_Trial_Users::where( 'id', $id )->update($update);

            $status = array( 'error_code' => 0, 'message'  => 'Your data has been changed!' );
            $counts = 1;
            DB::commit();
            Log::info('update_api linetrialuser beginTransaction - end');
        }
        catch(Exception $e){
            Log::error('update_api linetrialuser exception : ' . $e->getMessage());
            DB::rollback();
            Log::info('update_api linetrialuser beginTransaction - end(rollback)');
            echo "エラー：" . $e->getMessage();
            $counts = 0;
            $status = array( 'error_code' => 501, 'message'  => $e->getMessage() );
        }

        Log::info('update_api linetrialuser END');
        return response()->json([ compact('status','counts') ]);

    }

}
