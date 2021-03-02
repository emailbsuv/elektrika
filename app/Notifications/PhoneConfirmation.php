<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Log;
use NotificationChannels\SmscRu\SmscRuChannel;
use NotificationChannels\SmscRu\SmscRuMessage;

class PhoneConfirmation extends Notification
{
    use Queueable;

    protected $_code;

    /**
     * Create a new notification instance.
     *
     * @param $code
     */
    public function __construct($code)
    {
        $this->_code = $code;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        Log::info('PhoneConfirmation via');
        return [SmscRuChannel::class];
//        return ['mail'];
    }

    public function toSmscRu($notifiable)
    {
        Log::info('PhoneConfirmation toSmscRu');
//        return SmscRuMessage::create("Task #{$notifiable->id} is complete!");
        $appName = config('app.name');
        $hash = config('auth.hash');
        return SmscRuMessage::create("Your {$appName} code is: {$this->_code} {$hash}");
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        Log::info('PhoneConfirmation toArray');
        return [
            'phone' => $this->_phone,
            'code' => $this->_code,
        ];
    }
}
