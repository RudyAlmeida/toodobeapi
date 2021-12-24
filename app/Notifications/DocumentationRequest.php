<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\DocumentationRequests;

class DocumentationRequest extends Notification
{
    use Queueable;

    /**
     * @var DocumentationRequests
     */
    private $documentData;

    /**
     * DocumentationRequest constructor.
     * @param DocumentationRequests $documentData
     */
    public function __construct(DocumentationRequests $documentData)
    {
        $this->documentData = $documentData;
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
        $url = env('FRONTEND_URL', 'https://app.toodobe.com').'/#/documento/'. $this->documentData->id;
        return (new MailMessage)
            ->subject('Solicitação de documento: '. $this->documentData->document_name)
                    ->line('Para confirmar seus dados cadastrais precisamos que envie o sequinte documento: ' . $this->documentData->document_name)
                    ->action('Clique aqui para enviar', $url);
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
