<?php

namespace App\Listeners;

use App\Events\TwoFactorCodeEvent;
use App\Notifications\TwoFactorCode;
use Illuminate\Support\Facades\Log;

class TwoFactorCodeListener
{

    /**
     * Handle the event.
     *
     * @param TwoFactorCodeEvent $event
     * @return void
     */
    public function handle(TwoFactorCodeEvent $event)
    {
        try {
            $event->user->notify(new TwoFactorCode());
        } catch (\Throwable $e) {
            Log::error('TwoFactorCodeListener email notification error: ' . $e->getMessage());
        }
    }

}
