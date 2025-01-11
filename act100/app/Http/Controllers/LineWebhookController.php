<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Line_Message;
// use App\Models\Line_Trial_Users;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ImageComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\IconComponentBuilder;
use LINE\LINEBot\Constant\Flex\ComponentLayout;
use LINE\LINEBot\Constant\Flex\ComponentMargin;
use LINE\LINEBot\Constant\Flex\ComponentSpacing;
use LINE\LINEBot\Constant\Flex\ComponentButtonHeight;

class LineWebhookController extends Controller
{
    private $bot;
    private $channelToken;

    public function __construct()
    {
        // 1. LINE Bot SDKの初期化
        $this->channelToken = env('LINE_MESSAGE_CHANNEL_TOKEN');
    }

    //
    public function message(Request $request) {

        Log::info('LineWebhookController message START');

        $httpClient = new CurlHTTPClient(env('LINE_CHANNEL_ACCESS_TOKEN'));
        $bot = new LINEBot($httpClient, ['channelSecret' => env('LINE_CHANNEL_SECRET')]);

        $events = $request->events;
        
        foreach ($events as $event) {
            // 3. ユーザーID（送信先）を設定（例: $request->input('userId')で受け取る）
            // $userId = $request->input('userId'); // 例: POSTリクエストから取得
            $userId = $event['source']['userId']; // 例: POSTリクエストから取得

            if (isset($event['message']['type'])) {
                switch ($event['message']['type']) {
                    case 'text':
                        // Log::info('LineWebhookController message case text userId = ' . print_r($event['source']['userId'], true));

                        $replyToken = $event['replyToken'];
                        $userMessage = $event['message']['text'] ?? '';

                        // 2. Flexメッセージの作成
                        $hero = ImageComponentBuilder::builder()
                            ->setUrl('https://developers-resource.landpress.line.me/fx/img/01_1_cafe.png')
                            ->setSize('full')
                            ->setAspectRatio('20:13')
                            ->setAspectMode('cover')
                            ->setAction(
                                new UriTemplateActionBuilder(
                                    'Open URL',
                                    'https://line.me/'
                                )
                            );

                        $stars = [];
                        for ($i = 0; $i < 4; $i++) {
                            $stars[] = IconComponentBuilder::builder()
                                ->setSize('sm')
                                ->setUrl('https://developers-resource.landpress.line.me/fx/img/review_gold_star_28.png');
                        }
                        $stars[] = IconComponentBuilder::builder()
                            ->setSize('sm')
                            ->setUrl('https://developers-resource.landpress.line.me/fx/img/review_gray_star_28.png');
                        $stars[] = TextComponentBuilder::builder()
                            ->setText('4.0')
                            ->setSize('sm')
                            ->setColor('#999999')
                            ->setMargin(ComponentMargin::MD)
                            ->setFlex(0);

                        $ratingBox = BoxComponentBuilder::builder()
                            ->setLayout(ComponentLayout::BASELINE)
                            ->setMargin(ComponentMargin::MD)
                            ->setContents($stars);

                        $infoContents = [
                            BoxComponentBuilder::builder()
                                ->setLayout(ComponentLayout::BASELINE)
                                ->setSpacing(ComponentSpacing::SM)
                                ->setContents([
                                    TextComponentBuilder::builder()
                                        ->setText('Place')
                                        ->setColor('#aaaaaa')
                                        ->setSize('sm')
                                        ->setFlex(1),
                                    TextComponentBuilder::builder()
                                        ->setText('Flex Tower, 7-7-4 Midori-ku, Tokyo')
                                        ->setWrap(true)
                                        ->setColor('#666666')
                                        ->setSize('sm')
                                        ->setFlex(5),
                                ]),
                            BoxComponentBuilder::builder()
                                ->setLayout(ComponentLayout::BASELINE)
                                ->setSpacing(ComponentSpacing::SM)
                                ->setContents([
                                    TextComponentBuilder::builder()
                                        ->setText('Time')
                                        ->setColor('#aaaaaa')
                                        ->setSize('sm')
                                        ->setFlex(1),
                                    TextComponentBuilder::builder()
                                        ->setText('10:00 - 23:00')
                                        ->setWrap(true)
                                        ->setColor('#666666')
                                        ->setSize('sm')
                                        ->setFlex(5),
                                ]),
                        ];

                        $infoBox = BoxComponentBuilder::builder()
                            ->setLayout(ComponentLayout::VERTICAL)
                            ->setMargin(ComponentMargin::LG)
                            ->setSpacing(ComponentSpacing::SM)
                            ->setContents($infoContents);

                        $body = BoxComponentBuilder::builder()
                            ->setLayout(ComponentLayout::VERTICAL)
                            ->setContents([
                                TextComponentBuilder::builder()
                                    ->setText('Brown Cafe')
                                    ->setWeight('bold')
                                    ->setSize('xl'),
                                $ratingBox,
                                $infoBox,
                            ]);

                        $footer = BoxComponentBuilder::builder()
                            ->setLayout(ComponentLayout::VERTICAL)
                            ->setSpacing(ComponentSpacing::SM)
                            ->setContents([
                                ButtonComponentBuilder::builder()
                                    ->setStyle('link')
                                    ->setHeight(ComponentButtonHeight::SM)
                                    ->setAction(
                                        new UriTemplateActionBuilder(
                                            'CALL',
                                            'https://line.me/'
                                        )
                                    ),
                                ButtonComponentBuilder::builder()
                                    ->setStyle('link')
                                    ->setHeight(ComponentButtonHeight::SM)
                                    ->setAction(
                                        new UriTemplateActionBuilder(
                                            'WEBSITE',
                                            'https://line.me/'
                                        )
                                    ),
                            ]);

                        $bubble = BubbleContainerBuilder::builder()
                            ->setHero($hero)
                            ->setBody($body)
                            ->setFooter($footer);

                        $flexMessage = FlexMessageBuilder::builder()
                            ->setAltText('This is a Flex Message')
                            ->setContents($bubble);

                        // // 3. ユーザーID（送信先）を設定（例: $request->input('userId')で受け取る）
                        // // $userId = $request->input('userId'); // 例: POSTリクエストから取得
                        // ↑に $userId = $event['source']['userId']; // 例: POSTリクエストから取得

                        // 4. Flexメッセージを送信
                        $response = $bot->pushMessage($userId, $flexMessage);

                        Log::info('LineWebhookController message $userMessage = ' . print_r($userMessage, true));

                        // 5. レスポンスの確認
                        if ($response->isSucceeded()) {
                            Log::info('LineWebhookController response Reply succeeded: = ' . print_r($response->getHTTPStatus(), true));
                            Log::info('LineWebhookController message END');
                            return response()->json(['message' => 'Message sent successfully!']);
                        } else {
                            Log::info('LineWebhookController response Reply failed:   = ' . print_r($response->getRawBody(), true));
                            Log::info('LineWebhookController response Access Token:   = ' . print_r($this->accessToken, true));
                            Log::info('LineWebhookController response HTTP Status:    = ' . print_r($response->getHTTPStatus(), true));
                            Log::info('LineWebhookController response Error Message:  = ' . print_r($response->getRawBody(), true));
                            Log::info('LineWebhookController message END');
                            return response()->json([
                                'message' => 'Failed to send message',
                                'details' => $response->getRawBody(),
                            ], 500);
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
            'お問い合わせ',
            'お問い合わせ内容を選択してください。',
            'https://example.com/image.png',
            [
                new MessageTemplateActionBuilder('営業時間', '営業時間を教えてください'),
                new MessageTemplateActionBuilder('場所', '店舗の場所を教えてください')
            ]
        );

        $yes_button = new PostbackTemplateActionBuilder('はい', 'button=1');
        $no_button = new PostbackTemplateActionBuilder('キャンセル', 'button=0');
        $actions = [$yes_button, $no_button];
        $button = new ButtonTemplateBuilder('お問い合わせ', 'テキスト', '', $actions);

        $button_message = new TemplateMessageBuilder('お問い合わせ', $buttonTemplate);

        Log::info('LineWebhookController replyNormalQuery END');

        // $this->bot->replyMessage($replyToken, $button_message);
        // $this->bot->pushMessage($replyToken, $button_message);
        $this->sendReplyMessage($replyToken, $button_message);
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

        $this->bot->replyText($replyToken, $buttonsTemplate);
        // $this->sendReplyMessage($replyToken, $buttonsTemplate);     //the request body is invalid
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
