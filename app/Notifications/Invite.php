<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Invite extends Notification
{
    use Queueable;

    private $trackCode;

    private $userOwner;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($trackCode, $userOwner)
    {
       $this->trackCode = $trackCode;

       $this->userOwner = $userOwner;
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
        $url = env('APP_URL', 'https://api.toodobe.com').'/invite-track/'. $this->trackCode;

        return (new MailMessage)
            ->subject('Seu amigo '. $this->userOwner . ' te convidou para conhecer a ToodoBe')
            ->greeting('Olá!')
            ->line('Entre no link abaixo e saiba como prosperar conosco:')
            ->action('Entrar', $url);
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
