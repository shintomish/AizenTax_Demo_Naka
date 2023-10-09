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
	public function excel()
	{
        Log::info('ExcelMakeController excel START');
        
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

        $from_flcompany = 'global';
        $from_company   = '合同会社グローアップ・マネジメント';
        $from_repres    = '代表社員　　　　　富　澤　利　広';
        $tourokuno      = 'T9010503005932';

        // $to_company     = 'company-0005';       // 会社名
        // $to_represent   = 'represent-0005';     // 代表者名
        // $foloder_name   = 'folder0005';
        // $file_name      = now()->format('Ymd') .'_'. $from_company. '_'. $to_company. '_請求書';

        $xls_inp_data = $this->advisorsGet();
        $work_data = array(
            'to_company'   => array(),
            'to_represent' => array(),
            'foloder_name' => array(),
            'file_name'    => array(),
            'kanrino'      => array(),
            'tanka'        => array()
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
            $work_data['file_name']    = $first_ymd .'_'. $from_flcompany. '_'. $xls_data2->business_name. '様_請求書';  // ファイル名
            $str = sprintf("%05d", $xls_data2->customers_id);
            $work_data['kanrino']      = $year .'_'. $mon. '_'. $str;   // 管理番号
            $work_data['tanka']        = ($xls_data2->customers_id * 10) + 10000;
            array_push($xls_out_data, $work_data );
            $cnt = $cnt + 1;
        }
        // $xls_out_data = json_encode($xls_out_data);
        // $compacts = compact( 'advisorsfees', 'customers', 'nowyear', 'nowmonth' );
        // Log::debug('ExcelMakeController excel $xls_out_data = ' .print_r($xls_out_data,true));

        // App\Services\ExportService
        $export_service = new ExportService();
        foreach ($xls_out_data as $data) {
            $export_service->makeXlsPdf(
                $tourokuno,
                $tekiyou,
                $furibi,
                $from_company,
                $from_repres,
                $data['kanrino'],
                $data['tanka'],
                $data['to_company'], 
                $data['to_represent'], 
                $data['foloder_name'],
                $data['file_name']
            );
        }
 
        Log::info('ExcelMakeController excel END');

        return redirect()->route('advisorsfee.input');

    }

    public function advisorsGet()
    {
        Log::info('ExcelMakeController advisorsGet START');

        // ログインユーザーのユーザー情報を取得する
        $user    = $this->auth_user_info();
        $user_id = $user->id;
        
        $organization  = $this->auth_user_organization();
        $organization_id = $organization->id;

        // 年を取得2 
        $nowyear   = intval($this->get_now_year2());

        //今年の月を取得
        $nowmonth = intval($this->get_now_month());

        if($organization_id == 0) {
            $customers = Customer::where('organization_id','>=',$organization_id)
                            ->whereNull('deleted_at')
                            ->get();
            $advisorsfees = Advisorsfee::select(
                             'advisorsfees.id               as id'
                            ,'advisorsfees.organization_id  as organization_id'
                            ,'advisorsfees.custm_id         as custm_id'
                            ,'advisorsfees.year             as year'
                            ,'advisorsfees.advisor_fee      as advisor_fee'
                            ,'advisorsfees.fee_01        as fee_01'
                            ,'advisorsfees.fee_02        as fee_02'
                            ,'advisorsfees.fee_03        as fee_03'
                            ,'advisorsfees.fee_04        as fee_04'
                            ,'advisorsfees.fee_05        as fee_05'
                            ,'advisorsfees.fee_06        as fee_06'
                            ,'advisorsfees.fee_07        as fee_07'
                            ,'advisorsfees.fee_08        as fee_08'
                            ,'advisorsfees.fee_09        as fee_09'
                            ,'advisorsfees.fee_10        as fee_10'
                            ,'advisorsfees.fee_11        as fee_11'
                            ,'advisorsfees.fee_12        as fee_12'

                            ,'customers.id                  as customers_id'
                            ,'customers.business_name       as business_name'
                            ,'customers.represent_name      as represent_name'
                            ,'customers.foldername          as foldername'
                            ,'customers.individual_class    as individual_class'

                            )
                            ->leftJoin('customers', function ($join) {
                                $join->on('advisorsfees.custm_id', '=', 'customers.id');
                            })
                            ->whereNull('customers.deleted_at')
                            ->whereNull('advisorsfees.deleted_at')
                            ->where('advisorsfees.year','=',$nowyear)
                            ->orderBy('customers.individual_class', 'asc')
                            ->orderBy('customers.business_name', 'asc')
                            ->sortable()
                            ->get();
        } else {
            $customers = Customer::where('organization_id','=',$organization_id)
                            ->whereNull('deleted_at')
                            ->get();

            $advisorsfees = Advisorsfee::select(
                             'advisorsfees.id               as id'
                            ,'advisorsfees.organization_id  as organization_id'
                            ,'advisorsfees.custm_id         as custm_id'
                            ,'advisorsfees.year             as year'
                            ,'advisorsfees.advisor_fee      as advisor_fee'
                            ,'advisorsfees.fee_01        as fee_01'
                            ,'advisorsfees.fee_02        as fee_02'
                            ,'advisorsfees.fee_03        as fee_03'
                            ,'advisorsfees.fee_04        as fee_04'
                            ,'advisorsfees.fee_05        as fee_05'
                            ,'advisorsfees.fee_06        as fee_06'
                            ,'advisorsfees.fee_07        as fee_07'
                            ,'advisorsfees.fee_08        as fee_08'
                            ,'advisorsfees.fee_09        as fee_09'
                            ,'advisorsfees.fee_10        as fee_10'
                            ,'advisorsfees.fee_11        as fee_11'
                            ,'advisorsfees.fee_12        as fee_12'

                            ,'customers.id                  as customers_id'
                            ,'customers.business_name       as business_name'
                            ,'customers.represent_name      as represent_name'
                            ,'customers.foldername          as foldername'
                            ,'customers.individual_class    as individual_class'

                            )
                            ->leftJoin('customers', function ($join) {
                                $join->on('advisorsfees.custm_id', '=', 'customers.id');
                            })
                            ->where('advisorsfees.organization_id','=',$organization_id)
                            ->whereNull('customers.deleted_at')
                            ->whereNull('advisorsfees.deleted_at')
                            ->where('advisorsfees.year','=',$nowyear)
                            ->orderBy('customers.individual_class', 'asc')
                            ->orderBy('customers.business_name', 'asc')
                            ->sortable()
                            ->get();
        }

        // $compacts = compact( 'advisorsfees' );

        // Log::debug('ExcelMakeController advisorsGet $compacts = ' .print_r($compacts,true));
        Log::info('ExcelMakeController advisorsGet END');

        return $advisorsfees;
    }

}
