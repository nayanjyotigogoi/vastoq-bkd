<?php

namespace App\Filament\Resources\ContactMessageResource\Pages;

use App\Filament\Resources\ContactMessageResource;
use App\Mail\ContactReplyMail;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EditContactMessage extends EditRecord
{
    protected static string $resource = ContactMessageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $message = $this->record;

        if ($message->admin_reply && $message->email && !$message->reply_sent_at) {
            try {
                Mail::to($message->email)->send(new ContactReplyMail($message));
                $message->updateQuietly(['reply_sent_at' => now()]);
            } catch (\Throwable $e) {
                Log::error('[CONTACT REPLY] Email failed', [
                    'contact_message_id' => $message->id,
                    'error'              => $e->getMessage(),
                ]);
            }
        }
    }
}
