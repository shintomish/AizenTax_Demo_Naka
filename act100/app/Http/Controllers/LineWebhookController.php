<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Line_Message;
use App\Models\Line_Trial_Users;

use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;

class LineWebhookController extends Controller
{
    //
    public function message(Request $request) {

        Log::info('LineWebhookController message START');

        $data   = $request->all();
        $events = $data['events'];

        // composer require "linecorp/line-bot-sdk:9.*"
        // $client = new \GuzzleHttp\Client();
        // $config = new \LINE\Clients\MessagingApi\Configuration();
        // $config->setAccessToken(config('services.line.message.channel_token'));
        // $messagingApi = new \LINE\Clients\MessagingApi\Api\MessagingApiApi(
        //     client: $client,
        //     config: $config,
        // );
        // Log::debug('LineWebhookController message  events = ' . print_r($events,true));

        // composer require "linecorp/line-bot-sdk:7.*"
        $httpClient = new CurlHTTPClient(config('services.line.message.channel_token'));
        $bot = new LINEBot($httpClient, ['channelSecret' => config('services.line.message.channel_secret')]);

        foreach ($events as $event) {
            // メッセージの保存処理を追記
            // LineMessage::create([
            //     'line_user_id'    => $event['source']['userId'],
            //     'line_message_id' => $event['message']['id'],
            //     'text'            => $event['message']['text'],
            // ]);
            // $response = $bot->replyText($event['replyToken'], 'メッセージ送信完了');
            $line_message = new Line_Message();
            $line_message->line_user_id    = $event['source']['userId'];
            $line_message->line_message_id = $event['message']['id'];
            $line_message->text            = $event['message']['text'];
            $line_message->save();               //  Inserts

            $updata['count'] = Line_Trial_User::where('line_user_id', $line_message->line_user_id)->count();

            //何もしない
            if( $updata['count'] > 0 ) {

            //追加
            } else {
                $trial_user = new Line_Trial_Users();
                $trial_user->line_user_id    = $event['source']['userId'];
                $trial_user->users_name      = $event['message']['text'];
                $trial_user->save();               //  Inserts
            }
        }

        Log::info('LineWebhookController message END');
        return;
    }
}
