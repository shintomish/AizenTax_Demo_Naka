<?php

// 事務所 体験者データ確認
namespace App\Http\Controllers;

use App\Models\Line_Trial_Users_History;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LineTrialUserHistoryController extends Controller
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
    public function index()
    {
        Log::info('linetrialuserhistory index START');

        $line_trial_users_historys = Line_Trial_Users_History::whereNull('deleted_at')
                    ->where('extension_flg' , 2 )   // pdf
                    ->sortable()
                    ->orderBy('created_at', 'desc')
                    ->paginate(200);

        $line_trial_users_historys_count = Line_Trial_Users_History::whereNull('deleted_at')
                    ->where('extension_flg' , 2 )   // pdf
                    ->where('urgent_flg' ,    1 )   // 発行済
                    ->get();

        //発行済件数
        $count2     = $line_trial_users_historys_count->count();

        $common_no = 'linetrialuserhistory';

        Log::info('linetrialuserhistory index END');

        $compacts = compact( 'common_no', 'line_trial_users_historys' , 'count2' );

        return view( 'linetrialuserhistory.index', $compacts );
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show_up01($id)
    {
        Log::info('linetrialuserhistory show_up01 START');

        $line_trial_users_history = Line_Trial_Users_History::where('id',$id)
                    ->first();

        // Log::debug('linetrialuserhistory show_up01  line_trial_users_history = ' . print_r($line_trial_users_history,true));

        $disk = 'local';  // or 's3'
        $storage = Storage::disk($disk);

        $filepath = $line_trial_users_history->filepath;
        $filename = $line_trial_users_history->filename;
        $pdf_path = $filepath;

        // Log::debug('linetrialuserhistory show_up01  filename = ' . print_r($filename,true));
        // Log::debug('linetrialuserhistory show_up01  pdf_path = ' . print_r($pdf_path,true));

        $file = $storage->get($pdf_path);

        Log::info('linetrialuserhistory show_up01 END');

        // 拡張子フラグ(1):xlsx  (2):pdf
        if($line_trial_users_history->extension_flg == 1) {
            return response($file, 200)
                ->header('Content-Type', 'application/zip')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
        } else {
            return response($file, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
        }

    }
    /**
     * [webapi]billdataテーブルの更新
     */
    public function update_api(Request $request)
    {
        Log::info('update_api linetrialuserhistory START');

        // Log::debug('update_api request = ' .print_r($request->all(),true));
        $id = $request->input('id');

        $urgent_flg     = 1;  // 1:既読 2:未読

        $counts = array();
        $update = [];
        $update['urgent_flg'] = $urgent_flg;
        $update['updated_at'] = date('Y-m-d H:i:s');
        // Log::debug('update_api linetrialuserhistory update : ' . print_r($update,true));

        $status = array();
        DB::beginTransaction();
        Log::info('update_api linetrialuserhistory beginTransaction - start');
        try{
            // 更新処理
            Line_Trial_Users_History::where( 'id', $id )->update($update);

            $status = array( 'error_code' => 0, 'message'  => 'Your data has been changed!' );
            $counts = 1;
            DB::commit();
            Log::info('update_api linetrialuserhistory beginTransaction - end');
        }
        catch(Exception $e){
            Log::error('update_api linetrialuserhistory exception : ' . $e->getMessage());
            DB::rollback();
            Log::info('update_api linetrialuserhistory beginTransaction - end(rollback)');
            echo "エラー：" . $e->getMessage();
            $counts = 0;
            $status = array( 'error_code' => 501, 'message'  => $e->getMessage() );
        }

        Log::info('update_api linetrialuserhistory END');
        return response()->json([ compact('status','counts') ]);

    }

}
