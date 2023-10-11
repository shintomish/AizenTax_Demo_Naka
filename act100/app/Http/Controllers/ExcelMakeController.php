<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\Advisorsfee;

use Illuminate\Http\Request;

use App\Services\ExportService as ExportService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ExcelMakeController extends Controller
{
    //
	public function excel(Request $request)
	{
        Log::info('ExcelMakeController excel START');

        // ログインユーザーのユーザー情報を取得する
        // $user    = $this->auth_user_info();
        // $user_id = $user->id;

        $organization    = $this->auth_user_organization();
        $organization_id = $organization->id;

        // 年を取得2 
        $nowyear   = intval($this->get_now_year2());
        // $nowyear = $request->Input('year');

        //今年の月を取得
        $nowmonth = intval($this->get_now_month());

        $year     = now()->format('Y');
        $mon      = now()->format('m');
        $furiyear = now()->format('Y');
        $furimon1 = now()->format('m');
        if($furimon1 == 12) {
            $furimon1 = 1;
            $furiyear = $furiyear + 1;
        } else {
            $furimon1 = $furimon1 + 1;            
        }
        $furibi       = $furiyear . '年'. $furimon1. '月15日';

        $first_ymd    = now()->format('Ymd');
        $tekiyou      = now()->format('Y').'年'.now()->format('m').'月分　顧問料金';

        // $to_company     = 'company-0005';       // 会社名
        // $to_represent   = 'represent-0005';     // 代表者名
        // $foloder_name   = 'folder0005';
        // $file_name      = now()->format('Ymd') .'_'. $from_company. '_'. $to_company. '_請求書';

        // ret_query_count(): Queryを取得 Count用
        $count = $this->ret_query_count($organization_id,$nowyear,$nowmonth);
        if($count == 0) {
             
            Log::info('ExcelMakeController excel $count = 0 END');

            // toastrというキーでメッセージを格納　今月の請求データはありません
            session()->flash('toastr', config('toastr.invoice_warning'));

            return redirect()->route('advisorsfee.input');
        }

        Log::info('ExcelMakeController excel ret_query_count = ' .print_r($count,true));

        // 選択クエリーオブジェクトを返す
        $xls_inp_data = $this->advisorsGet($request,$organization_id,$nowyear,$nowmonth);

        $work_data = array(
            'to_company'   => array(),
            'to_represent' => array(),
            'from_company' => array(),
            'from_repres'  => array(),
            'foloder_name' => array(),
            'file_name'    => array(),
            'kanrino'      => array(),
            'tanka'        => array(),
            'customers_id' => array()
        );

        $xls_out_data      = array();
        $cnt = 1;
        foreach ($xls_inp_data as $xls_data2) {

            if($xls_data2->individual_class == 1) {
                $work_data['to_company']    = $xls_data2->business_name. ' 御中';     // 会社名
                $work_data['to_represent']  = $xls_data2->represent_name;             // 代表者名
            } else {
                $work_data['to_company']    = '';                                     // 会社名　Null
                $work_data['to_represent']  = $xls_data2->business_name;              // 会社名
            }
            $work_data['foloder_name'] = $xls_data2->foldername;        // フォルダー名

            // 契約主体
            if($xls_data2->contract_entity == 1) {
                $work_data['from_flcompany'] = '合同会社グローアップ';
                $work_data['from_company']   = '合同会社グローアップ・マネジメント';
                $work_data['from_repres']    = '代表社員　　　　　富　澤　利　広';
                $work_data['tourokuno']      = 'T9010503005932';
            } else {
                $work_data['from_flcompany'] = '税理士法人_間庭';
                $work_data['from_company']   = '税理士法人 間庭・飯田合同事務所';
                $work_data['from_repres']    = '代表社員　　　　　税 理 士 法 人';
                $work_data['tourokuno']      = 'T9010501234567';             
            }

            $work_data['customers_id']      = $xls_data2->customers_id;   
            $cusid = sprintf("%05d", $xls_data2->customers_id);

            // ファイル名 20231011_合同会社グローアップ_000xx_請求書
            $stringw  = $first_ymd;
            $stringw .= '_'. $work_data['from_flcompany'];
            $stringw .= '_'. $cusid. '_請求書';

            $string  = preg_replace("/( |　)/", "", $stringw ); //文字列の中にある半角空白と全角空白をすべて削除・除去する
            $work_data['file_name']    = $string;
            $work_data['kanrino']      = $year .'_'. $mon. '_'. $cusid;   // 管理番号

            if($nowmonth == 10) {
                $work_data['tanka']    = ($xls_data2->fee_10);
            }
            // $work_data['tanka']        = ($xls_data2->customers_id * 10) + 10000;

            array_push($xls_out_data, $work_data );
            $cnt = $cnt + 1;
        }

        // Log::debug('ExcelMakeController excel $xls_out_data = ' .print_r($xls_out_data,true));

        // App\Services\ExportService
        $export_service = new ExportService();
        foreach ($xls_out_data as $data) {
            $export_service->makeXlsPdf(
                $data['tourokuno'],
                $tekiyou,
                $furibi,
                $data['from_company'],
                $data['from_repres'],
                $data['kanrino'],
                $data['tanka'],
                $data['to_company'], 
                $data['to_represent'], 
                $data['foloder_name'],
                $data['file_name'],
                $data['customers_id']
            );
        }
 
        Log::info('ExcelMakeController excel END');

        // toastrというキーでメッセージを格納　請求データ作成処理が正常に完了しました
        session()->flash('toastr', config('toastr.invoice_success'));

        return redirect()->route('advisorsfee.input');

    }

    // 選択クエリーオブジェクトを返す
    public function advisorsGet(Request $request,$organization_id,$nowyear,$nowmonth)
    {
        Log::info('ExcelMakeController advisorsGet START');

        $query = $this->ret_query($organization_id,$nowyear,$nowmonth);
        $advisorsfees = DB::select($query);

        // Log::debug('ExcelMakeController advisorsGet $query = ' .print_r($query,true));


        Log::info('ExcelMakeController advisorsGet END');

        return $advisorsfees;

    }

    /**
     *    ret_query()      : Queryを取得
     *    $organization_id : 組織ID
     *    $nowyear         : 選択年
     *    $nowmonth        : 当月
     */
    public function ret_query($organization_id,$nowyear,$nowmonth) 
    {
        Log::info('ExcelMakeController ret_query START');

        $fee_name = '';
        if($nowmonth == 10) {
            $fee_name =  'fee_10';
        }

        // select sql contract_entity
        $query = '';
        $query .= 'select ';
        $query .= 'advisorsfees.id               as id ,';
        $query .= 'advisorsfees.organization_id  as organization_id ,';
        $query .= 'advisorsfees.custm_id         as custm_id ,';
        $query .= 'advisorsfees.year             as year ,';
        $query .= 'advisorsfees.contract_entity  as contract_entity ,';
        $query .= 'advisorsfees.advisor_fee      as advisor_fee ,';
        $query .= 'advisorsfees.fee_01        as fee_01 ,';
        $query .= 'advisorsfees.fee_02        as fee_02 ,';
        $query .= 'advisorsfees.fee_03        as fee_03 ,';
        $query .= 'advisorsfees.fee_04        as fee_04 ,';
        $query .= 'advisorsfees.fee_05        as fee_05 ,';
        $query .= 'advisorsfees.fee_06        as fee_06 ,';
        $query .= 'advisorsfees.fee_07        as fee_07 ,';
        $query .= 'advisorsfees.fee_08        as fee_08 ,';
        $query .= 'advisorsfees.fee_09        as fee_09 ,';
        $query .= 'advisorsfees.fee_10        as fee_10 ,';
        $query .= 'advisorsfees.fee_11        as fee_11 ,';
        $query .= 'advisorsfees.fee_12        as fee_12 ,';
        $query .= 'customers.id                  as customers_id ,';
        $query .= 'customers.business_name       as business_name ,';
        $query .= 'customers.represent_name      as represent_name ,';
        $query .= 'customers.foldername          as foldername ,';
        $query .= 'customers.individual_class    as individual_class ';
        $query .=  ' FROM advisorsfees JOIN customers ON advisorsfees.custm_id=customers.id ';
        $query .=  ' where ';

        if($organization_id == 0) {
            $query .=  ' (advisorsfees.organization_id >= %organization_id%) AND';
        } else {
            $query .=  ' (advisorsfees.organization_id = %organization_id%) AND';
        }
        
        $query .=  ' (advisorsfees.%fee_name% > 0) AND';
    
        $query .=  ' (customers.deleted_at is NULL ) AND ';
        $query .=  ' (advisorsfees.deleted_at is NULL ) AND ';
        $query .=  ' (advisorsfees.year = %nowyear% ) ';
        $query .=  ' order By customers.id asc ';
        $query  = str_replace('%organization_id%', $organization_id, $query);
        $query  = str_replace('%nowyear%',         $nowyear,         $query);
        $query  = str_replace('%fee_name%',        $fee_name,        $query);

        Log::info('ExcelMakeController ret_query END');

        return $query;
    }

    /**
     *    ret_query_count(): Queryを取得 Count用
     *    $organization_id : 組織ID
     *    $nowyear         : 選択年
     *    $nowmonth        : 当月
     */
    public function ret_query_count($organization_id,$nowyear,$nowmonth) 
    {
        Log::info('ExcelMakeController ret_query_count START');

        $fee_name = '';
        if($nowmonth == 10) {
            $fee_name =  'fee_10';
        }

        // count sql
        $query = '';
        $query .= 'select count(*) AS count ';
        $query .= 'from advisorsfees ';
        $query .= 'where deleted_at is NULL AND ';
        if($organization_id == 0) {
            $query .=  ' (advisorsfees.organization_id >= %organization_id%) AND';
        } else {
            $query .=  ' (advisorsfees.organization_id = %organization_id%) AND';
        }
        
        $query .=  ' (advisorsfees.%fee_name% > 0) AND';

        $query .=  ' (advisorsfees.deleted_at is NULL ) AND ';
        $query .=  ' (advisorsfees.year = %nowyear% ) ';

        // $query .=  ' GROUP BY id; ';

        $query  = str_replace('%organization_id%', $organization_id, $query);
        $query  = str_replace('%nowyear%',         $nowyear,         $query);
        $query  = str_replace('%fee_name%',        $fee_name,        $query);

        $advisorsfees = DB::select($query);
        $count        = $advisorsfees[0]->count;

        Log::info('ExcelMakeController ret_query_count END');        

        // Log::debug('ExcelMakeController ret_query_count $query = ' .print_r($query,true));
        // Log::debug('ExcelMakeController ret_query_count $count = ' .print_r($count,true));

        return $count;
    }

    public function advisorsGet_org() {

        // if($organization_id == 0) {
        //     $advisorsfees = Advisorsfee::select(
        //                      'advisorsfees.id               as id'
        //                     ,'advisorsfees.organization_id  as organization_id'
        //                     ,'advisorsfees.custm_id         as custm_id'
        //                     ,'advisorsfees.year             as year'
        //                     ,'advisorsfees.advisor_fee      as advisor_fee'
        //                     ,'advisorsfees.fee_01        as fee_01'
        //                     ,'advisorsfees.fee_02        as fee_02'
        //                     ,'advisorsfees.fee_03        as fee_03'
        //                     ,'advisorsfees.fee_04        as fee_04'
        //                     ,'advisorsfees.fee_05        as fee_05'
        //                     ,'advisorsfees.fee_06        as fee_06'
        //                     ,'advisorsfees.fee_07        as fee_07'
        //                     ,'advisorsfees.fee_08        as fee_08'
        //                     ,'advisorsfees.fee_09        as fee_09'
        //                     ,'advisorsfees.fee_10        as fee_10'
        //                     ,'advisorsfees.fee_11        as fee_11'
        //                     ,'advisorsfees.fee_12        as fee_12'

        //                     ,'customers.id                  as customers_id'
        //                     ,'customers.business_name       as business_name'
        //                     ,'customers.represent_name      as represent_name'
        //                     ,'customers.foldername          as foldername'
        //                     ,'customers.individual_class    as individual_class'

        //                     )
        //                     ->leftJoin('customers', function ($join) {
        //                         $join->on('advisorsfees.custm_id', '=', 'customers.id');
        //                     })
        //                     ->whereNull('customers.deleted_at')
        //                     ->whereNull('advisorsfees.deleted_at')
        //                     ->where('advisorsfees.year','=',$nowyear)
        //                     ->orderBy('customers.id', 'asc')
        //                     ->get();
        // } else {
        //     $advisorsfees = Advisorsfee::select(
        //                      'advisorsfees.id               as id'
        //                     ,'advisorsfees.organization_id  as organization_id'
        //                     ,'advisorsfees.custm_id         as custm_id'
        //                     ,'advisorsfees.year             as year'
        //                     ,'advisorsfees.advisor_fee      as advisor_fee'
        //                     ,'advisorsfees.fee_01        as fee_01'
        //                     ,'advisorsfees.fee_02        as fee_02'
        //                     ,'advisorsfees.fee_03        as fee_03'
        //                     ,'advisorsfees.fee_04        as fee_04'
        //                     ,'advisorsfees.fee_05        as fee_05'
        //                     ,'advisorsfees.fee_06        as fee_06'
        //                     ,'advisorsfees.fee_07        as fee_07'
        //                     ,'advisorsfees.fee_08        as fee_08'
        //                     ,'advisorsfees.fee_09        as fee_09'
        //                     ,'advisorsfees.fee_10        as fee_10'
        //                     ,'advisorsfees.fee_11        as fee_11'
        //                     ,'advisorsfees.fee_12        as fee_12'

        //                     ,'customers.id                  as customers_id'
        //                     ,'customers.business_name       as business_name'
        //                     ,'customers.represent_name      as represent_name'
        //                     ,'customers.foldername          as foldername'
        //                     ,'customers.individual_class    as individual_class'

        //                     )
        //                     ->leftJoin('customers', function ($join) {
        //                         $join->on('advisorsfees.custm_id', '=', 'customers.id');
        //                     })
        //                     ->where('advisorsfees.organization_id','=',$organization_id)
        //                     ->whereNull('customers.deleted_at')
        //                     ->whereNull('advisorsfees.deleted_at')
        //                     ->where('advisorsfees.year','=',$nowyear)
        //                     ->orderBy('customers.id', 'asc')
        //                     ->get();
        // }

    }

}