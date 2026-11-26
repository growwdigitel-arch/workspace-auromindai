<?php

namespace App\Listeners;

use App\Events\NewMentionChatEvent;
use App\Notifications\NewMentionChat;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NewMentionChatListener
{

    /**
     * Handle the event.
     *
     * @param NewMentionChatEvent $event
     * @return void
     */

    public function handle(NewMentionChatEvent $event)
    {
        try {
            if ($event->notifyUser) {
                Notification::send($event->notifyUser, new NewMentionChat($event->userChat));
            }
        } catch (\Throwable $e) {
            Log::error('NewMentionChatListener notification error: ' . $e->getMessage());
        }
    }

}
