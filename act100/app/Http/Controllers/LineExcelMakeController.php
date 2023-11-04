<?php

namespace App\Http\Controllers;

use App\Models\Line_Trial_User;

// use Illuminate\Http\Request;

use App\Services\LineExportService as LineExportService;
use Illuminate\Support\Facades\Log;


class LineExcelMakeController extends Controller
{
    //
	public function lineexcel($id)
	{
        Log::info('LineExcelMakeController lineexcel START');

        // ログインユーザーのユーザー情報を取得する
        // $user    = $this->auth_user_info();
        // $user_id = $user->id;

        $organization    = $this->auth_user_organization();
        $organization_id = $organization->id;

        // 年を取得2
        $nowyear   = intval($this->get_now_year2());

        //今年の月を取得
        $nowmonth = intval($this->get_now_month());

        $filename = now()->format('Y-m-d');
        $year     = now()->format('Y');
        $mon      = now()->format('m');
        $day      = now()->format('d');

        // 配列初期化
        $work_data = array(
            'nowyear'   => array(),
            'nowmonth'  => array(),
            'user_id'   => array(),
            'user_name' => array(),
            'kanrino'   => array(),
            'file_name' => array()
        );

        $xls_out_data      = array();

        $xls_inp_data = Line_Trial_User::where('id','=', $id)
                ->first();

        $cnt = 1;
        foreach ($xls_inp_data as $xls_data2) {

            $cusid = sprintf("%02d", $id);
            $work_data['nowyear']      = $nowyear;        // 年
            $work_data['nowmonth']     = $nowmonth;       // 月
            $work_data['user_id']      = $id;             // id
            $work_data['user_name']    = $$xls_data2->user_name;             // ユーザー名
            $work_data['kanrino']      = 'R-'.$year .$mon. $day. '_'. $cusid; // 管理番号 R-231112-xx

            $stringw  = $filename;
            $stringw .= '_'. $cusid;
            $stringw .= '_'. $work_data['user_name'];
            $stringw .= '_'. $cusid. '_請求書';
            $string   = preg_replace("/( |　)/", "", $stringw );         // 文字列の中にある半角空白と全角空白をすべて削除・除去する
            $work_data['file_name']    = $string;

            array_push($xls_out_data, $work_data );
            $cnt = $cnt + 1;
        }

        // Log::debug('LineExcelMakeController lineexcel $xls_out_data = ' .print_r($xls_out_data,true));

        // App\Services\LineExportService
        $export_service = new LineExportService();
            /**
             *    LinemakeXlsPdf() : Excelを作成しPDFに変換
             *    $nowyear         : 年
             *    $nowmonth        : 月
             *    $user_id         : ユーザーID
             *    $user_name       : 体験者名
             *    $kanrino         : 管理番号 No
             *    $file_name       : ファイル名
             */
        foreach ($xls_out_data as $data) {
            $export_service->LinemakeXlsPdf(
                $data['nowyear'],
                $data['nowmonth'],
                $data['user_id'],
                $data['user_name'],
                $data['kanrino'],
                $data['file_name'],
            );
        }

        Log::info('LineExcelMakeController lineexcel END');

        // toastrというキーでメッセージを格納　請求データ作成処理が正常に完了しました
        session()->flash('toastr', config('toastr.invoice_success'));

        return redirect()->route('advisorsfee.input');

    }

}
