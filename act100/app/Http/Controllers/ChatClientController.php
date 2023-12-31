<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\User;
use App\Models\Customer;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index() {

        Log::info('ChatClientController index START');

        // ログインユーザーのユーザー情報を取得する
        $user  = $this->auth_user_info();
        $u_id = $user->id;
        $organization_id =  $user->organization_id;

        //FileNameは「latestinformation.pdf」固定 2022/09/24
        $books = DB::table('books')->first();
        $str   = ( new DateTime($books->info_date))->format('Y-m-d');
        $latestinfodate = '最新情報'.'('.$str.')';

        // Customer(複数レコード)情報を取得する
        $customer_findrec = $this->auth_customer_findrec();
        $customer_id = $customer_findrec[0]['id'];
        // 2023/03/17
        $indiv_class = $customer_findrec[0]['individual_class'];

        $jsonfile = storage_path() . "/tmp/customer_info_status_". $customer_id. ".json";

        // // Log::debug('ChatClientController index  $customer_id = ' . print_r($customer_id,true));
        // $staff_id = 1;     //事務所Staffのusers.id
        // $user_id = $user->user_id;

        $messages = Message::select(
                'messages.id              as id'
                // ,'messages.organization_id as organization_id'
                ,'messages.user_id         as user_id'
                ,'messages.customer_id     as customer_id'
                ,'messages.body            as m_body'
                ,'messages.created_at      as m_created_at'
                ,'users.id                 as users_id'
                ,'users.user_id            as users_custom_id'
                ,'users.name               as users_name'
                ,'customers.id             as customers_id'
                ,'customers.business_name  as business_name'
            )
            ->leftJoin('users', function ($join) {
                $join->on('messages.user_id', '=', 'users.id');
            })
            ->leftJoin('customers', function ($join) {
                $join->on('messages.customer_id', '=', 'customers.id');
            })
            ->whereNull('customers.deleted_at')
            ->orderBy('messages.id', 'desc')
            ->orderBy('messages.customer_id', 'asc')
            ->paginate(300);

        $users = User::where('organization_id','=',$organization_id)
                        ->where('login_flg','=', 1 )  //顧客
                        ->whereNull('deleted_at')
                        ->get();

        $customers = Customer::where('organization_id','=',$organization_id)
                        ->whereNull('deleted_at')
                        ->get();

        $common_no = '00_7';
        $compacts = compact( 'indiv_class','messages','common_no','users','customers','customer_findrec','customer_id','jsonfile','latestinfodate' );

        Log::info('ChatClientController index END');

        return view('chatclient.index', $compacts );

        // return Message::orderBy('id', 'desc')->get();
        // $messages = Message::orderBy('id', 'desc')->get();
        // $messages = Message::with('user')->orderBy('id', 'desc')->get();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function serch(Request $request)
    {
        Log::info('ChatClientController serch START');

        //-------------------------------------------------------------
        //- Request パラメータ
        //-------------------------------------------------------------
        $customer_id = $request->Input('customer_id');

        // ログインユーザーのユーザー情報を取得する
        $user  = $this->auth_user_info();
        $u_id = $user->id;
        $organization_id =  $user->organization_id;

        $users = User::where('organization_id','=',$organization_id)
                        ->where('login_flg','=', 1 )  //顧客
                        ->whereNull('deleted_at')
                        ->get();

        // Customer(複数レコード)情報を取得する
        $customer_findrec = $this->auth_customer_findrec();
        // $customer_id = $customer_findrec[0]['id'];

        // 2022/11/30
        $customers = Customer::where('id',$customer_id)
            ->orderBy('id', 'asc')
            ->first();

        // 2022/11/30
        $indiv_class = $customers->individual_class;

        //FileNameは「latestinformation.pdf」固定 2022/09/24
        $books = DB::table('books')->first();
        $str   = ( new DateTime($books->info_date))->format('Y-m-d');
        $latestinfodate = '最新情報'.'('.$str.')';

        $jsonfile = storage_path() . "/tmp/customer_info_status_". $customer_id. ".json";

        $messages = Message::select(
            'messages.id              as id'
            // ,'messages.organization_id as organization_id'
            ,'messages.user_id         as user_id'
            ,'messages.customer_id     as customer_id'
            ,'messages.body            as m_body'
            ,'messages.created_at      as m_created_at'
            ,'users.id                 as users_id'
            ,'users.user_id            as users_custom_id'
            ,'users.name               as users_name'
            ,'customers.id             as customers_id'
            ,'customers.business_name  as business_name'
        )
        ->leftJoin('users', function ($join) {
            $join->on('messages.user_id', '=', 'users.id');
        })
        ->leftJoin('customers', function ($join) {
            $join->on('messages.customer_id', '=', 'customers.id');
        })
        ->where('customers.id','=','customer_id')
        ->whereNull('customers.deleted_at')
        ->orderBy('messages.id', 'desc')
        ->orderBy('messages.customer_id', 'asc')
        ->paginate(300);

        $common_no = '00_7';
        $compacts = compact( 'indiv_class','messages','common_no','users','customers','customer_findrec','customer_id','jsonfile','latestinfodate' );

        Log::info('ChatClientController serch END');

        return view('chatclient.index', $compacts );

    }

}
