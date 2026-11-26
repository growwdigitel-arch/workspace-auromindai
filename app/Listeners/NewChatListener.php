<?php

namespace App\Listeners;

use App\Events\NewChatEvent;
use App\Models\User;
use App\Notifications\NewChat;
use App\Scopes\ActiveScope;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NewChatListener
{

    /**
     * Handle the event.
     *
     * @param NewChatEvent $event
     * @return void
     */

    public function handle(NewChatEvent $event)
    {
        try {
            $notifyUser = User::withoutGlobalScope(ActiveScope::class)->find($event->userChat->user_id);
            if ($notifyUser) {
                Notification::send($notifyUser, new NewChat($event->userChat));
            }
        } catch (\Throwable $e) {
            Log::error('NewChatListener notification error: ' . $e->getMessage());
        }
    }

}
