<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Line_Message;
use App\Models\Line_Trial_Users;

// use LINE\LINEBot;
// use LINE\LINEBot\HTTPClient\CurlHTTPClient;
// use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
// use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
// use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
// use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;

use GuzzleHttp\Client;

class LineWebhookController extends Controller
{
    private $bot;
    private $accessToken;
    private $accessSecret;

    public function __construct()
    {
         // config() を使用
        $this->accessToken = config('app.accessToken');
        $this->accessSecret = config('app.secret');

        // \Log::info('__construct Access Token: ' . $this->accessToken);
        // \Log::info('__construct Secret: ' . $this->accessSecret);

        $httpClient = new CurlHTTPClient($this->accessToken);
        $this->bot = new LINEBot($httpClient, ['channelSecret' => $this->accessSecret]);
    }

    //
    public function message(Request $request) {

        Log::info('LineWebhookController message START');

        $events = $request->events;

        foreach ($events as $event) {

            if (isset($event['message']['type'])) {
                switch ($event['message']['type']) {
                    case 'text':

                        $replyToken = $event['replyToken'];
                        $userMessage = $event['message']['text'] ?? '';

                        \Log::info('message replyToken: ' . $replyToken);

                        // 分岐処理
                        if (str_contains($userMessage, '価格')) {
                            //Call to a member function build() on array at /var/www/html/actver/vendor/linecorp/line-bot-sdk/src/LINEBot/MessageBuilder/FlexMessageBuilder.php:142)
                            $this->replyPriceQuery($replyToken);

                            // 商品価格を返信するロジック
                            // $this->replyPriceMessage($replyToken, $userMessage);      // OK
                        } elseif (str_contains($userMessage, '問い合わせ')) {
                            $this->replyNormalQuery($replyToken);
                            // $this->replyNormalMessage($replyToken);      // OK
                        } else {
                            $this->replyDefault($replyToken);
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

        $flexContent = [
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
        ];

        // FlexMessageBuilderのインスタンスを生成
        $flexMessage = new FlexMessageBuilder('商品価格リスト', $flexContent);

        $response = $this->bot->replyMessage($replyToken, $flexMessage);

        if (!$response->isSucceeded()) {
            Log::info('LineWebhookController replyPriceQuery Reply failed:   = ' . print_r($response->getRawBody(), true));
            Log::info('LineWebhookController replyPriceQuery Access Token:   = ' . print_r($this->accessToken, true));
            Log::info('LineWebhookController replyPriceQuery HTTP Status:    = ' . print_r($response->getHTTPStatus(), true));
            Log::info('LineWebhookController replyPriceQuery Error Message:  = ' . print_r($response->getRawBody(), true));
        } else {
            Log::info('LineWebhookController replyPriceQuery HTTP Status:    = ' . print_r($response->getHTTPStatus(), true));
        }

        Log::info('LineWebhookController replyPriceQuery END');
    }

    private function replyNormalQuery($replyToken)
    {
        Log::info('LineWebhookController replyNormalQuery START');

        $buttonTemplate = new ButtonTemplateBuilder(
            'タイトル', // 最大40文字
            '説明文', // 最大160文字
            'https://www.tax-trial.com/sample.jpg', // HTTPS形式の有効な画像URL
            [
                new MessageTemplateActionBuilder('ボタン1', 'アクション1'), // ボタンの表示名とアクション内容
                new UriTemplateActionBuilder('詳細はこちら', 'https://www.tax-trial.com') // ボタンの表示名とURL
            ]
        );

        $templateMessage = new TemplateMessageBuilder('こちらはボタンテンプレートです', $buttonTemplate);

        // Log::info('LineWebhookController replyNormalQuery templateMessage:  = ' . print_r($templateMessage, true));

        $response = $this->bot->replyMessage($replyToken, $templateMessage);

        if (!$response->isSucceeded()) {
            Log::info('LineWebhookController replyNormalQuery Reply failed:   = ' . print_r($response->getRawBody(), true));
            Log::info('LineWebhookController replyNormalQuery Access Token:   = ' . print_r($this->accessToken, true));
            Log::info('LineWebhookController replyNormalQuery HTTP Status:    = ' . print_r($response->getHTTPStatus(), true));
            Log::info('LineWebhookController replyNormalQuery Error Message:  = ' . print_r($response->getRawBody(), true));
        } else {
            Log::info('LineWebhookController replyNormalQuery HTTP Status:    = ' . print_r($response->getHTTPStatus(), true));
        }

        Log::info('LineWebhookController replyNormalQuery END');
    }

    private function replyDefault($replyToken)
    {
        Log::info('LineWebhookController replyDefault START');

        \Log::info('replyDefault Access Token: ' . $this->accessToken);

        $message = new TextMessageBuilder('申し訳ありませんが、そのリクエストには対応できません。');

        $response = $this->bot->replyMessage($replyToken, $message);
        if (!$response->isSucceeded()) {
            Log::info('LineWebhookController replyDefault Reply failed:   = ' . print_r($response->getRawBody(), true));
            Log::info('LineWebhookController replyDefault Access Token:   = ' . print_r($this->accessToken, true));
            Log::info('LineWebhookController replyDefault HTTP Status:    = ' . print_r($response->getHTTPStatus(), true));
            Log::info('LineWebhookController replyDefault Error Message:  = ' . print_r($response->getRawBody(), true));
        } else {
            Log::info('LineWebhookController replyNormalQuery HTTP Status:    = ' . print_r($response->getHTTPStatus(), true));
        }

        Log::info('LineWebhookController replyDefault END');

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
            'Authorization' => "Bearer {$this->accessToken}",
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
