<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Line_Message;
use App\Models\Line_Trial_Users;

// use LINE\LINEBot;
// use LINE\LINEBot\HTTPClient\CurlHTTPClient;
// use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
// use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
// use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
// use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
// use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
// use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;

use GuzzleHttp\Client;

class LineWebhookController extends Controller
{
    private $bot;

    public function __construct()
    {
        // .envからアクセストークンを取得してプロパティに格納
        $this->channelToken = env('LINE_MESSAGE_CHANNEL_TOKEN');
        $httpClient = new CurlHTTPClient(env('LINE_CHANNEL_ACCESS_TOKEN'));
        $this->bot = new LINEBot($httpClient, ['channelSecret' => env('LINE_CHANNEL_SECRET')]);

    }

    //
    public function message(Request $request) {

        Log::info('LineWebhookController message START');

        // $data   = $request->all();
        // $events = $data['events'];
        // $httpClient = new CurlHTTPClient(config('services.line.message.channel_token'));
        // $bot = new LINEBot($httpClient, ['channelSecret' => config('services.line.message.channel_secret')]);

        $events = $request->events;

        foreach ($events as $event) {

            if (isset($event['message']['type'])) {
                switch ($event['message']['type']) {
                    case 'text':
                        // Log::info('LineWebhookController message case text userId = ' . print_r($event['source']['userId'], true));

                        // $line_message = new Line_Message();
                        // $line_message->line_user_id    = $event['source']['userId'];
                        // $line_message->line_message_id = $event['message']['id'];
                        // $line_message->text            = $event['message']['text'];
                        // $line_message->save();               //  Inserts

                        // $events = $request->input('events');
                        // $replyToken = $event['replyToken'];
                        // $userMessage = $event['message']['text'];

                        // // メッセージ分類
                        // if (strpos($userMessage, '価格') !== false) {
                        //     $this->replyPriceMessage($replyToken, $userMessage);
                        // } else {
                        //     $this->replyNormalMessage($replyToken);
                        // }

                        $replyToken = $event['replyToken'];
                        $userMessage = $event['message']['text'] ?? '';

                        // 分岐処理
                        if (str_contains($userMessage, '価格')) {
                            // $this->replyPriceQuery($event);
                            // $this->replyPriceMessage($event,$userMessage);
                            // 商品価格を返信するロジック
                            $this->replyPriceMessage($replyToken, $userMessage);
                        } elseif (str_contains($userMessage, '問い合わせ')) {
                            $this->replyNormalQuery($event);
                            // $this->replyNormalMessage($replyToken);      // OK
                        } else {
                            $this->replyDefault($event);
                        }

                        Log::info('LineWebhookController message $userMessage = ' . print_r($userMessage, true));

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
        // return 'ok';
        return response()->json(['status' => 'success']);
    }


    private function replyPriceQuery($replyToken)
    {
        Log::info('LineWebhookController replyPriceQuery START');

        // $replyToken = $event['replyToken'];

        $flexMessage = new FlexMessageBuilder('商品価格リスト', [
            'type' => 'bubble',
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => '価格リスト',
                        'weight' => 'bold',
                        'size' => 'xl'
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'margin' => 'lg',
                        'spacing' => 'sm',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => '商品A: ¥1,000'
                            ],
                            [
                                'type' => 'text',
                                'text' => '商品B: ¥2,000'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        Log::info('LineWebhookController replyPriceQuery END');
        // $this->bot->replyMessage($replyToken, $flexMessage);
        $this->bot->pushMessage($replyToken, $flexMessage);
    }

    private function replyNormalQuery($event)
    {
        Log::info('LineWebhookController replyNormalQuery START');

        $replyToken = $event['replyToken'];

        $buttonTemplate = new ButtonTemplateBuilder(
            'タイトル', // タイトル
            '説明文です', // 説明文
            // 'https://example.com/sample.jpg', // サムネイル画像のURL
            [
                new MessageTemplateActionBuilder('ボタン1', 'アクション1'),
                new UriTemplateActionBuilder('ボタン2', 'https://example.com')
            ]
        );

        $templateMessage = new TemplateMessageBuilder('こちらはテンプレートメッセージです', $buttonTemplate);
        Log::info('LineWebhookController replyNormalQuery $userMessage = ' . print_r($userMessage, true));
        Log::info('LineWebhookController replyNormalQuery END');

        $this->bot->replyMessage($replyToken, $templateMessage);

    }

    private function replyDefault($event)
    {
        Log::info('LineWebhookController replyDefault START');

        $replyToken = $event['replyToken'];
        $message = new TextMessageBuilder('申し訳ありませんが、そのリクエストには対応できません。');

        Log::info('LineWebhookController replyDefault END');

        $this->bot->replyMessage($replyToken, $message);        //OKだが resなし
        // $this->bot->pushMessage($replyToken, $message);      //OKだが resなし
        // $this->bot->replyText($replyToken, $message);        //OKだが resなし
    }


    private function replyPriceMessage($replyToken, $userMessage)
    {
        Log::info('LineWebhookController replyPriceMessage START');

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
        Log::info('LineWebhookController replyPriceMessage END');

        // $bot->replyText($replyToken, $flexMessage);
        $this->sendReplyMessage($replyToken, $flexMessage);
    }

    private function replyNormalMessage($replyToken)
    {
        Log::info('LineWebhookController replyNormalMessage START');
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

        Log::info('LineWebhookController replyNormalMessage END');

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
