<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InformationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     * __construct() でInformationモデル (=お知らせ) を受け取る
     */
    public function __construct()
    {
        //
        $this->information = $information;
    }

    /**
     * Get the notification's delivery channels.
     * via() では ['database'] を返す
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // return ['mail'];
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     * メール通知は使用しないのでtoMail()を削除
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     return (new MailMessage)
    //                 ->line('The introduction to the notification.')
    //                 ->action('Notification Action', url('/'))
    //                 ->line('Thank you for using our application!');
    // }

    /**
     * Get the array representation of the notification.
     * toArray() メソッドで通知に使用したいデータを配列で返す
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
            'date' => $this->information->date,
            'title' => $this->information->title,
            'content' => $this->information->content,
             //  通知からリンクしたいURLがあれば設定しておくと便利
            'url' => route('infos.show', ['information' => $this->information])
        ];
    }
}
