<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBilling extends Notification
{
    use Queueable;

    protected $billId;

    /**
     * Create a new notification instance.
     *
     * @param $billing
     */
    public function __construct($billing)
    {
        $this->billId = $billing->id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = env('FRONTEND_URL', 'https://app.toodobe.com').'/#//detalhes-cobranca/'. $this->billId;

        return (new MailMessage)
            ->subject('Nova Cobrança')
            ->greeting('Olá!')
            ->line('Uma nova cobrança foi gerada para você')
            ->action('Vizualizar Cobrança', $url);

    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
