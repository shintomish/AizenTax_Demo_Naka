<?php

// 事務所 体験者データ確認
namespace App\Http\Controllers;

use App\Models\Line_Trial_Users_History;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $line_trial_users_history = DB::table('Line_Trial_Users_History')
                    ->orderBy('created_at', 'desc')
                    ->sortable()
                    ->paginate(100);

        $common_no = 'linetrialuserhistory';

        Log::info('linetrialuserhistory index END');

        $compacts = compact( 'common_no', 'line_trial_users_history' );

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
        Log::info('invoicehistory show_up01 START');

        $line_trial_users_history = Line_Trial_Users_History::where('id',$id)
                    ->first();

        // Log::debug('invoicehistory show_up01  line_trial_users_history = ' . print_r($line_trial_users_history,true));

        $disk = 'local';  // or 's3'
        $storage = Storage::disk($disk);

        $filepath = $line_trial_users_history->filepath;
        $filename = $line_trial_users_history->filename;
        $pdf_path = $filepath;

        // Log::debug('invoicehistory show_up01  filename = ' . print_r($filename,true));
        // Log::debug('invoicehistory show_up01  pdf_path = ' . print_r($pdf_path,true));

        $file = $storage->get($pdf_path);

        Log::info('invoicehistory show_up01 END');

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

}
