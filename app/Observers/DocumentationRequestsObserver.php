<?php

namespace App\Observers;

use App\DocumentationRequests;
use App\Notifications\DocumentationRequest as Notification;
use App\User;
use App\Notifications\DocumentationRequestRefused;

class DocumentationRequestsObserver
{
    /**
     * Handle the documentation requests "created" event.
     *
     * @param  \App\DocumentationRequests  $documentationRequests
     * @return void
     */
    public function created(DocumentationRequests $documentationRequests)
    {
        $this->sendNotification($documentationRequests);
    }

    /**
     * Handle the documentation requests "updated" event.
     *
     * @param  \App\DocumentationRequests  $documentationRequests
     * @return void
     */
    public function updated(DocumentationRequests $documentationRequests)
    {
        if($documentationRequests->document_status == "recusado"){
            $this->sendRefusedNotification($documentationRequests);
        }
    }

    /**
     * Handle the documentation requests "deleted" event.
     *
     * @param  \App\DocumentationRequests  $documentationRequests
     * @return void
     */
    public function deleted(DocumentationRequests $documentationRequests)
    {
        //
    }

    /**
     * Handle the documentation requests "restored" event.
     *
     * @param  \App\DocumentationRequests  $documentationRequests
     * @return void
     */
    public function restored(DocumentationRequests $documentationRequests)
    {
        //
    }

    /**
     * Handle the documentation requests "force deleted" event.
     *
     * @param  \App\DocumentationRequests  $documentationRequests
     * @return void
     */
    public function forceDeleted(DocumentationRequests $documentationRequests)
    {
        //
    }

    /**
     * @param DocumentationRequests $documentationRequests
     */
    private function sendNotification(DocumentationRequests $documentationRequests)
    {
        $user = $this->getUser($documentationRequests->user_id);

        $user->notify(
            new Notification($documentationRequests)
        );
    }
    private function sendRefusedNotification(DocumentationRequests $documentationRequests)
    {
        $user = $this->getUser($documentationRequests->user_id);

        $user->notify(
            new DocumentationRequestRefused($documentationRequests)
        );
    }

    /**
     * @param $user_id
     * @return mixed
     */
    private function getUser($user_id)
    {
        return User::find($user_id);
    }
}
