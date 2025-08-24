<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Line_Message;
use App\Models\Line_Trial_Users;

use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;
use GuzzleHttp\Client;

class LineWebhookController extends Controller
{
    public function __construct()
    {
        // .envからアクセストークンを取得してプロパティに格納
        $this->channelToken = env('LINE_MESSAGE_CHANNEL_TOKEN');
    }

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
        // Log::debug('LineWebhookController message $events = ' . print_r($events,true));

        // composer require "linecorp/line-bot-sdk:7.*"
        $httpClient = new CurlHTTPClient(config('services.line.message.channel_token'));
        $bot = new LINEBot($httpClient, ['channelSecret' => config('services.line.message.channel_secret')]);

        foreach ($events as $event) {

            if (isset($event['message']['type'])) {
                switch ($event['message']['type']) {
                    case 'text':
                        Log::info('LineWebhookController message case text userId = ' . print_r($event['source']['userId'], true));

                        // メッセージの保存処理を追記
                        $line_message = new Line_Message();
                        $line_message->line_user_id    = $event['source']['userId'];
                        $line_message->line_message_id = $event['message']['id'];
                        $line_message->text            = $event['message']['text'];
                        $line_message->save();               //  Inserts

                        $updata['count'] = Line_Trial_Users::where('line_user_id', $event['source']['userId'])->count();
                        if( $updata['count'] == 0 ) {
                            $trial_user = new Line_Trial_Users();
                            $trial_user->line_user_id    = $line_message->line_user_id;
                            $trial_user->users_name      = $line_message->text;
                            $trial_user->save();               //  Inserts

                            $msg = "体験会ご予約承りました。" . "\n". "\n";
                            $msg .= "担当者より随時ご案内いたします。" . "\n";
                            $msg .= "今しばらくお待ちください。";
                            $bot->replyText($event['replyToken'], $msg);
                        }
                        else{
                            $events = $request->input('events');
                            $replyToken = $event['replyToken'];
                            $userMessage = $event['message']['text'];

                            // メッセージ分類
                            if (strpos($userMessage, '価格') !== false) {
                                $this->replyPriceMessage($replyToken, $userMessage);
                            } else {
                                $this->replyNormalMessage($replyToken);
                            }
                            Log::info('LineWebhookController message case text $userMessage = ' . print_r($userMessage, true));

                            return response()->json(['status' => 'success']);
                        }

                        break;
                    case 'image':
                        break;
                        // スタンプが送信された場合
                    case 'sticker':
                        break;
                    default :
                        Log::info('LineWebhookController message case default userId = ' . print_r($event['source']['userId'], true));
                        break;
                }
            }
        }

        Log::info('LineWebhookController message END');
        return 'ok';
    }
    private function replyPriceMessage($replyToken, $userMessage)
    {
        // 商品名を抽出 (例: "価格 シャンプー")
        $productName = str_replace('価格 ', '', $userMessage);
        $price = $this->getPriceByProductName($productName);

        $flexMessage = [
            'type' => 'flex',
            'altText' => '価格情報',
            'contents' => [
                'type' => 'bubble',
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => "商品名: $productName",
                            'weight' => 'bold',
                            'size' => 'xl',
                        ],
                        [
                            'type' => 'text',
                            'text' => "価格: ¥$price",
                            'size' => 'md',
                            'color' => '#555555',
                        ],
                    ],
                ],
            ],
        ];

        $this->sendReplyMessage($replyToken, $flexMessage);
    }

    private function replyNormalMessage($replyToken)
    {
        $buttonsTemplate = [
            'type' => 'template',
            'altText' => '問い合わせメニュー',
            'template' => [
                'type' => 'buttons',
                // 'thumbnailImageUrl' => 'https://example.com/image.jpg',
                'title' => 'お問い合わせ',
                'text' => '選択してください',
                'actions' => [
                    [
                        'type' => 'message',
                        'label' => '価格問い合わせ',
                        'text' => '価格'
                    ],
                    [
                        'type' => 'message',
                        'label' => 'その他問い合わせ',
                        'text' => 'その他'
                    ],
                ],
            ],
        ];

        $this->sendReplyMessage($replyToken, $buttonsTemplate);
    }

    private function getPriceByProductName($productName)
    {
        // ダミー価格データ (本番ではデータベースやAPIを参照)
        $prices = [
            'シャンプー' => 1200,
            'リンス' => 1500,
            '美容液' => 3000,
        ];
        // return $prices[$productName] . " です。" ?? '未登録です。';
        return $prices[$productName] . " です。" ?? '商品の価格が見つかりません。';

    }

    private function sendReplyMessage($replyToken, $message)
    {
        $client = new Client();
        $url = 'https://api.line.me/v2/bot/message/reply';
        $headers = [
            'Authorization' => "Bearer {$this->channelToken}",
            'Content-Type' => 'application/json',
        ];
        $body = [
            'replyToken' => $replyToken,
            'messages' => [$message],
        ];
        // Log::info('LineWebhookController sendReplyMessage $headers = ' . print_r($headers, true));

        $client->post($url, [
            'headers' => $headers,
            'json' => $body,
        ]);
    }


}
