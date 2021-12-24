<?php

namespace App\Observers;

use App\Billings;
use App\Notifications\NewBilling;
use App\User;

class BillingObserver
{
    /**
     * Handle the billings "created" event.
     *
     * @param Billings $billings
     * @return void
     */
    public function created(Billings $billings)
    {
        $this->sendNotification($billings);
    }

    /**
     * Handle the billings "updated" event.
     *
     * @param Billings $billings
     * @return void
     */
    public function updated(Billings $billings)
    {

    }

    /**
     * Handle the billings "deleted" event.
     *
     * @param Billings $billings
     * @return void
     */
    public function deleted(Billings $billings)
    {
        //
    }

    /**
     * Handle the billings "restored" event.
     *
     * @param Billings $billings
     * @return void
     */
    public function restored(Billings $billings)
    {
        //
    }

    /**
     * Handle the billings "force deleted" event.
     *
     * @param Billings $billings
     * @return void
     */
    public function forceDeleted(Billings $billings)
    {
        //
    }

    /**
     * @param Billings $billings
     */
    private function sendNotification(Billings $billings)
    {
        $user = $this->getUser($billings->user_id);

        $user->notify(
            new NewBilling($billings)
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
